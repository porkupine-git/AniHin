//minified

!function(){const t="global_script_ad",e=3e5,c=6e4,o=15e3,n="https://pl29154972.effectivecpmnetwork.com/f5/cf/1e/f5cf1e0c887624cd55af2c361dc23f43.js",a="global_smartlink",l=12e4,s="https://www.effectivecpmnetwork.com/sm49j0yu?key=a1a84dcfe0b3a8145e50ea702a006324",i="__AD_MANAGER__";function r(t,e=null){try{const c=localStorage.getItem(i+t);return c?JSON.parse(c):e}catch{return e}}function f(t,e){localStorage.setItem(i+t,JSON.stringify(e))}function d(){return Date.now()}function u(){let a=r(t,{startedAt:0,activeUntil:0,lastLoop:0});const l=d();l>a.activeUntil&&l-a.startedAt>=e&&(a.startedAt=l,a.activeUntil=l+c,a.lastLoop=0,f(t,a),console.log("NEW AD SESSION STARTED")),l<a.activeUntil&&l-a.lastLoop>=o&&(!function(){if(document.querySelector('script[data-global-ad="1"]'))return;const t=document.createElement("script");t.src=n,t.async=!0,t.setAttribute("data-global-ad","1"),document.body.appendChild(t),console.log("Script ad injected")}(),a.lastLoop=l,f(t,a),console.log("AD LOOP RUNNING"))}u(),setInterval(()=>{u()},5e3),["click","touchstart"].forEach(t=>{document.addEventListener(t,function(){!function(){const t=r(a,0),e=d();e-t<l||(f(a,e),window.open(s,"_blank"),console.log("SMARTLINK OPENED"))}()},{passive:!0,capture:!0})})}();

(function () {

    // =========================
    // CONFIG
    // =========================

    const SCRIPT_AD = {

        id: "global_script_ad",

        // Start every 5 min
        interval: 5 * 60 * 1000,

        // Keep active for 1 min
        duration: 1 * 60 * 1000,

        // Run every 15 sec while active
        loopEvery: 15000,

        src:
            "https://po.bebangcaromel.com/rdHDnWXa6oFj0gurE/143348"

    };

    const SMARTLINK = {

        id: "global_smartlink",

        // Popup once every 2 min
        cooldown: 2 * 60 * 1000,

        url:
            "https://jp.duskedaruac.com/iVINzA5inPx/143349"

    };

    // =========================
    // STORAGE
    // =========================

    const PREFIX =
        "__AD_MANAGER__";

    function get(
        key,
        fallback = null
    ) {

        try {

            const value =
                localStorage.getItem(
                    PREFIX + key
                );

            return value
                ? JSON.parse(value)
                : fallback;

        } catch {

            return fallback;

        }

    }

    function set(
        key,
        value
    ) {

        localStorage.setItem(
            PREFIX + key,
            JSON.stringify(value)
        );

    }

    function now() {

        return Date.now();

    }

    // =========================
    // SCRIPT AD
    // =========================

    function injectScriptAd() {

        const existing =
            document.querySelector(
                'script[data-global-ad="1"]'
            );

        if (existing) {
            return;
        }

        const s =
            document.createElement(
                "script"
            );

        s.src =
            SCRIPT_AD.src;

        s.async = true;

        s.type =
            "text/javascript";

        s.setAttribute(
            "data-cfasync",
            "false"
        );

        s.setAttribute(
            "data-global-ad",
            "1"
        );

        document.body.appendChild(s);

    }

    function handleScriptAd() {

        let state = get(
            SCRIPT_AD.id,
            {
                startedAt: 0,
                activeUntil: 0,
                lastLoop: 0
            }
        );

        const current =
            now();

        // =========================
        // START NEW SESSION
        // =========================

        if (

            current >
            state.activeUntil &&

            current -
            state.startedAt >=
            SCRIPT_AD.interval

        ) {

            state.startedAt =
                current;

            state.activeUntil =
                current +
                SCRIPT_AD.duration;

            state.lastLoop = 0;

            set(
                SCRIPT_AD.id,
                state
            );

        }

        // =========================
        // ACTIVE PERIOD
        // =========================

        if (
            current <
            state.activeUntil
        ) {

            if (

                current -
                state.lastLoop >=
                SCRIPT_AD.loopEvery

            ) {

                injectScriptAd();

                state.lastLoop =
                    current;

                set(
                    SCRIPT_AD.id,
                    state
                );

            }

        }

    }

    // Run instantly
    handleScriptAd();

    // Keep checking
    setInterval(
        handleScriptAd,
        5000
    );

    // =========================
    // SMARTLINK
    // =========================

    function openSmartlink() {

        const lastOpen =
            get(
                SMARTLINK.id,
                0
            );

        const current =
            now();

        // Cooldown active
        if (

            current -
            lastOpen <
            SMARTLINK.cooldown

        ) {

            return;

        }

        set(
            SMARTLINK.id,
            current
        );

        // Open instantly to bypass blockers
        const newTab =
            window.open(
                "about:blank",
                "_blank"
            );

        // Redirect opened tab
        if (newTab) {

            newTab.location =
                SMARTLINK.url;

        } else {

            // Fallback if popup blocked
            window.location.href =
                SMARTLINK.url;

        }

    }

    // =========================
    // EVENTS
    // =========================

    [
        "click",
        "touchstart"
    ].forEach(eventType => {

        document.addEventListener(

            eventType,

            openSmartlink,

            {
                passive: true,
                capture: true
            }

        );

    });

})();

// non minified
/* (function () {

  // =========================
  // CONFIG
  // =========================

  const SCRIPT_AD = {
    id: "global_script_ad",

    // Start every 5 min
    interval: 5 * 60 * 1000,

    // Keep active for 1 min
    duration: 1 * 60 * 1000,

    // Run every 15 sec while active
    loopEvery: 15000,

    src: "https://pl29154972.effectivecpmnetwork.com/f5/cf/1e/f5cf1e0c887624cd55af2c361dc23f43.js"
  };

  const SMARTLINK = {
    id: "global_smartlink",

    // Popup once every 5 min
    cooldown: 5 * 60 * 1000,

    url: "https://www.effectivecpmnetwork.com/sm49j0yu?key=a1a84dcfe0b3a8145e50ea702a006324"
  };

  // =========================
  // STORAGE
  // =========================

  const PREFIX = "__AD_MANAGER__";

  function get(key, fallback = null) {
    try {
      const value = localStorage.getItem(PREFIX + key);
      return value ? JSON.parse(value) : fallback;
    } catch {
      return fallback;
    }
  }

  function set(key, value) {
    localStorage.setItem(PREFIX + key, JSON.stringify(value));
  }

  function now() {
    return Date.now();
  }

  // =========================
  // SCRIPT AD
  // =========================

  function injectScriptAd() {

    // Prevent duplicate inject
    const existing =
      document.querySelector(
        'script[data-global-ad="1"]'
      );

    if (existing) {
      return;
    }

    const s = document.createElement("script");

    s.src =
      SCRIPT_AD.src;

    s.async = true;

    s.setAttribute(
      "data-global-ad",
      "1"
    );

    document.body.appendChild(s);

    console.log("Script ad injected");
  }

  function handleScriptAd() {

    let state = get(
      SCRIPT_AD.id,
      {
        startedAt: 0,
        activeUntil: 0,
        lastLoop: 0
      }
    );

    const current = now();

    // =========================
    // START NEW SESSION
    // =========================

    if (
      current > state.activeUntil &&
      current - state.startedAt >= SCRIPT_AD.interval
    ) {

      state.startedAt = current;

      state.activeUntil =
        current + SCRIPT_AD.duration;

      state.lastLoop = 0;

      set(SCRIPT_AD.id, state);

      console.log("NEW AD SESSION STARTED");
    }

    // =========================
    // ACTIVE PERIOD
    // =========================

    if (current < state.activeUntil) {

      // Prevent too frequent execution
      if (
        current - state.lastLoop >=
        SCRIPT_AD.loopEvery
      ) {

        injectScriptAd();

        state.lastLoop = current;

        set(SCRIPT_AD.id, state);

        console.log("AD LOOP RUNNING");
      }
    }
  }

  // Run instantly
  handleScriptAd();

  // Keep checking
  setInterval(() => {
    handleScriptAd();
  }, 5000);

  // =========================
  // SMARTLINK
  // =========================

  function openSmartlink() {

    const lastOpen =
      get(SMARTLINK.id, 0);

    const current =
      now();

    // Cooldown active
    if (
      current - lastOpen <
      SMARTLINK.cooldown
    ) {
      return;
    }

    set(
      SMARTLINK.id,
      current
    );

    window.open(
      SMARTLINK.url,
      "_blank"
    );

    console.log("SMARTLINK OPENED");
  }

  // Open on first click/touch
  [
    "click",
    "touchstart"
  ].forEach(eventType => {

    document.addEventListener(
      eventType,
      function () {
        openSmartlink();
      },
      {
        passive: true,
        capture: true
      }
    );

  });

})();*/