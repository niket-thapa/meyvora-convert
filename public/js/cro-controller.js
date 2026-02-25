/**
 * CRO Popup Controller
 *
 * Main orchestrator that coordinates:
 * - Signal collection
 * - Decision making (via REST API)
 * - Popup rendering
 * - Event tracking
 */
(function () {
  "use strict";

  const CROController = {
    // Configuration
    config: {
      restUrl: "",
      nonce: "",
      debug: false,
      visitorState: {},
      context: {},
    },

    // State
    state: {
      initialized: false,
      popupShown: false,
      currentCampaign: null,
      signalCollector: null,
      decisionPending: false,
      pageviewId: null,
    },

    // Trigger handlers
    triggers: {},

    /**
     * Initialize the controller
     */
    init: function (config) {
      if (this.state.initialized) return;

      this.config = { ...this.config, ...config };

      // Don't run on admin or if disabled
      if (this.shouldSkip()) {
        this.log("Skipping initialization");
        return;
      }

      this.log("Initializing CRO Controller");

      // One pageview ID per page load (for A/B impression dedupe; same ID sent with every decide request)
      this.state.pageviewId = this.getPageviewId();

      // Initialize signal collector
      this.initSignalCollector();

      // Set up trigger listeners
      this.initTriggers();

      // Listen for manual triggers
      this.initManualTriggers();

      this.state.initialized = true;

      // Dispatch ready event
      document.dispatchEvent(new CustomEvent("cro:ready"));
    },

    /**
     * Check if we should skip initialization
     */
    shouldSkip: function () {
      // Skip on admin pages
      if (document.body.classList.contains("wp-admin")) return true;

      // Skip if disabled via config
      if (this.config.disabled) return true;

      // Skip on checkout (always)
      if (this.config.context.page?.type === "checkout") return true;

      return false;
    },

    /**
     * Generate a stable pageview ID for this page load (used for A/B impression dedupe).
     * One ID per load; same ID sent with every decide request on this page.
     */
    getPageviewId: function () {
      if (typeof crypto !== "undefined" && crypto.randomUUID) {
        return crypto.randomUUID();
      }
      return "pv-" + Date.now() + "-" + Math.random().toString(36).slice(2, 11);
    },

    /**
     * Initialize signal collector
     */
    initSignalCollector: function () {
      if (typeof CROSignalCollector !== "undefined") {
        this.state.signalCollector = new CROSignalCollector();

        // Listen for signals
        document.addEventListener("cro:signal", (e) => {
          this.onSignal(e.detail);
        });
      }
    },

    /**
     * Initialize trigger listeners
     */
    initTriggers: function () {
      const self = this;

      // Exit Intent Trigger
      this.triggers.exit_intent = {
        active: false,
        init: function () {
          if (this.active) return;
          this.active = true;

          // Desktop: mouse leave from top
          document.addEventListener("mouseout", function (e) {
            if (e.clientY <= 0 && !e.relatedTarget) {
              self.onTrigger("exit_intent", { type: "mouse_exit" });
            }
          });

          // Mobile: handled via signals (scroll up, back button)
        },
      };

      // Scroll Trigger
      this.triggers.scroll = {
        active: false,
        threshold: 50,
        triggered: false,
        init: function (threshold) {
          if (this.active) return;
          this.active = true;
          this.threshold = threshold || 50;

          window.addEventListener(
            "scroll",
            self.throttle(() => {
              if (this.triggered) return;

              const scrollPercent = self.getScrollPercent();
              if (scrollPercent >= this.threshold) {
                this.triggered = true;
                self.onTrigger("scroll", { depth: scrollPercent });
              }
            }, 100)
          );
        },
      };

      // Time Trigger
      this.triggers.time = {
        active: false,
        timeout: null,
        init: function (seconds) {
          if (this.active) return;
          this.active = true;

          this.timeout = setTimeout(() => {
            self.onTrigger("time", { seconds: seconds });
          }, seconds * 1000);
        },
      };

      // Inactivity Trigger
      this.triggers.inactivity = {
        active: false,
        timeout: null,
        lastActivity: Date.now(),
        init: function (seconds) {
          if (this.active) return;
          this.active = true;

          const checkInactivity = () => {
            const idle = (Date.now() - this.lastActivity) / 1000;
            if (idle >= seconds) {
              self.onTrigger("inactivity", { idle_seconds: idle });
            } else {
              this.timeout = setTimeout(checkInactivity, 1000);
            }
          };

          // Reset on activity
          ["mousemove", "keydown", "scroll", "click", "touchstart"].forEach(
            (event) => {
              document.addEventListener(
                event,
                () => {
                  this.lastActivity = Date.now();
                },
                { passive: true }
              );
            }
          );

          this.timeout = setTimeout(checkInactivity, 1000);
        },
      };

      // Click Trigger
      this.triggers.click = {
        active: false,
        init: function (selector) {
          if (this.active || !selector) return;
          this.active = true;

          document.addEventListener("click", function (e) {
            if (e.target.matches(selector) || e.target.closest(selector)) {
              e.preventDefault();
              self.onTrigger("click", { selector: selector });
            }
          });
        },
      };

      // Initialize default trigger (exit intent)
      this.triggers.exit_intent.init();

      // Fire page_load once so campaigns set to "show on load" or time with short delay get evaluated
      setTimeout(function () {
        if (!self.state.popupShown && !self.state.decisionPending) {
          self.onTrigger("page_load", {});
        }
      }, 500);

      // Time trigger: fire at 1, 3, 10, 30, 60 seconds so time-delay campaigns get evaluated
      var timeCheckpoints = [1, 3, 10, 30, 60];
      timeCheckpoints.forEach(function (seconds) {
        setTimeout(function () {
          if (!self.state.popupShown && !self.state.decisionPending) {
            self.onTrigger("time", { seconds: seconds });
          }
        }, seconds * 1000);
      });

      // Activate scroll trigger: fire when user reaches 25%, 50%, 75%, 100% so scroll-depth campaigns get evaluated
      var scrollFired = {};
      window.addEventListener(
        "scroll",
        self.throttle(function () {
          if (self.state.popupShown || self.state.decisionPending) return;
          var pct = self.getScrollPercent();
          [25, 50, 75, 100].forEach(function (threshold) {
            if (pct >= threshold && !scrollFired[threshold]) {
              scrollFired[threshold] = true;
              self.onTrigger("scroll", { depth: pct });
            }
          });
        }, 200),
        { passive: true }
      );

      // Activate inactivity trigger (30s default) so inactivity campaigns get evaluated
      this.triggers.inactivity.init(30);
    },

    /**
     * Initialize manual trigger points
     */
    initManualTriggers: function () {
      const self = this;

      // Allow manual trigger via data attributes
      document.querySelectorAll("[data-cro-trigger]").forEach((el) => {
        el.addEventListener("click", function (e) {
          const campaignId = this.dataset.croCampaign;
          if (campaignId) {
            e.preventDefault();
            self.showCampaign(parseInt(campaignId));
          }
        });
      });

      // Global trigger function
      window.croTrigger = function (campaignId) {
        self.showCampaign(campaignId);
      };
    },

    /**
     * Handle signal from collector
     */
    onSignal: function (signalData) {
      this.log("Signal received:", signalData.type);

      // Check if this signal should trigger evaluation
      const exitSignals = [
        "exit_mouse",
        "scroll_up_fast",
        "tab_blur",
        "back_button",
      ];

      if (exitSignals.includes(signalData.type)) {
        this.onTrigger("exit_intent", signalData);
      }
    },

    /**
     * Handle trigger event
     */
    onTrigger: function (triggerType, data) {
      if (this.state.popupShown || this.state.decisionPending) {
        this.log("Trigger ignored - popup shown or decision pending");
        return;
      }

      this.log("Trigger fired:", triggerType, data);

      // Request decision from server
      this.requestDecision(triggerType, data);
    },

    /**
     * Get REST API base URL (absolute). Uses config.restUrl or falls back to /wp-json/.
     */
    getRestUrl: function () {
      var base = this.config.restUrl || "";
      if (!base || base.indexOf("wp-json") === -1) {
        var origin =
          typeof window !== "undefined" && window.location
            ? window.location.origin
            : "";
        base = origin ? origin + "/wp-json/" : "/wp-json/";
      }
      return base.replace(/\/?$/, "/");
    },

    /**
     * Request decision from server
     */
    requestDecision: function (triggerType, triggerData) {
      this.state.decisionPending = true;

      const signals = this.state.signalCollector
        ? this.state.signalCollector.getSignals()
        : {};

      const behavior = this.state.signalCollector
        ? this.state.signalCollector.getSignals()
        : {};

      const requestData = {
        trigger_type: triggerType,
        trigger_data: triggerData,
        signals: signals,
        behavior: behavior,
        context: this.config.context,
        pageview_id: this.state.pageviewId || undefined,
      };

      this.log("Requesting decision:", requestData);

      fetch(this.getRestUrl() + "cro/v1/decide", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestData),
      })
        .then((response) => response.json())
        .then((decision) => {
          this.state.decisionPending = false;
          this.handleDecision(decision);
        })
        .catch((error) => {
          this.state.decisionPending = false;
          this.log("Decision error:", error);
        });
    },

    /**
     * Handle decision response
     */
    handleDecision: function (decision) {
      this.log("Decision received:", decision);

      this.state.decisionPending = false;

      if (decision.show && decision.campaign) {
        if (this.state.popupShown) {
          this.log("Popup already shown, skipping");
          return;
        }
        if (typeof CROPopup === "undefined") {
          this.log(
            "CROPopup not available – ensure cro-popup.js is loaded before cro-controller"
          );
          return;
        }
        this.state.popupShown = true;
        this.state.decisionPending = true;
        try {
          this.showPopup(decision.campaign);
        } catch (err) {
          this.log("Error showing popup:", err);
          this.state.popupShown = false;
        }
        this.state.decisionPending = false;
      } else {
        this.log("Decision: do not show -", decision.reason);
      }

      // Debug mode
      if (this.config.debug && decision.debug) {
        console.group("CRO Decision Debug");
        console.log("Decision:", decision);
        console.log("Debug Log:", decision.debug);
        console.groupEnd();
      }
    },

    /**
     * Show a specific campaign by ID
     */
    showCampaign: function (campaignId) {
      fetch(this.getRestUrl() + "cro/v1/campaign/" + campaignId, {
        headers: {
          "X-WP-Nonce": this.config.nonce,
        },
      })
        .then((response) => response.json())
        .then((campaign) => {
          if (campaign && campaign.id) {
            this.state.popupShown = true;
            this.showPopup(campaign);
          }
        })
        .catch((error) => {
          this.log("Error loading campaign:", error);
        });
    },

    /**
     * Show popup (popupShown is already set by handleDecision to prevent race).
     */
    showPopup: function (campaign) {
      this.state.currentCampaign = campaign;

      this.log("Showing popup:", campaign.name);

      // Ensure only one popup: remove ANY existing overlay and popup (including not-yet-visible)
      document
        .querySelectorAll("body > .cro-overlay, body > .cro-popup")
        .forEach(function (el) {
          el.remove();
        });
      document.body.classList.remove("cro-popup-open");

      // Create popup instance
      const popup = new CROPopup(campaign, {
        onShow: () => {
          this.trackEvent("impression", campaign.id);
          document.dispatchEvent(
            new CustomEvent("cro:campaign_shown", {
              detail: { campaignId: campaign.id, campaignName: campaign.name },
            })
          );
        },
        onClose: (reason) => {
          this.state.popupShown = false;
          this.state.currentCampaign = null;

          if (reason === "dismiss") {
            this.trackEvent("dismiss", campaign.id);
            document.dispatchEvent(
              new CustomEvent("cro:campaign_dismissed", {
                detail: { campaignId: campaign.id },
              })
            );
          }
        },
        onConvert: (type, data) => {
          this.trackEvent("conversion", campaign.id, { type, data });
          document.dispatchEvent(
            new CustomEvent("cro:campaign_converted", {
              detail: { campaignId: campaign.id, conversionType: type },
            })
          );
        },
        onEmailCapture: (email) => {
          this.trackEvent("email_capture", campaign.id, { email });
          document.dispatchEvent(
            new CustomEvent("cro:email_captured", {
              detail: { email },
            })
          );
        },
      });

      popup.show();
    },

    /**
     * Track event
     */
    trackEvent: function (eventType, campaignId, data = {}) {
      const eventData = {
        event_type: eventType,
        campaign_id: campaignId,
        page_url: window.location.href,
        timestamp: Date.now(),
        ...data,
      };

      // Send to server (Blob with application/json so server parses body; sendBeacon with string uses text/plain)
      const blob = new Blob([JSON.stringify(eventData)], {
        type: "application/json",
      });
      navigator.sendBeacon(this.getRestUrl() + "cro/v1/track", blob);
    },

    /**
     * Utility: Get scroll percentage
     */
    getScrollPercent: function () {
      const h = document.documentElement;
      const b = document.body;
      const st = "scrollTop";
      const sh = "scrollHeight";
      return ((h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight)) * 100;
    },

    /**
     * Utility: Throttle function
     */
    throttle: function (func, limit) {
      let inThrottle;
      return function (...args) {
        if (!inThrottle) {
          func.apply(this, args);
          inThrottle = true;
          setTimeout(() => (inThrottle = false), limit);
        }
      };
    },

    /**
     * Utility: Log with prefix
     */
    log: function (...args) {
      if (this.config.debug) {
        console.log("[CRO]", ...args);
      }
    },
  };

  // Expose globally
  window.CROController = CROController;

  // Auto-initialize when config is available
  document.addEventListener("DOMContentLoaded", function () {
    if (window.croConfig) {
      CROController.init(window.croConfig);
    }
  });
})();
