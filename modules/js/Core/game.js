/**
 * BGA framework override
 * AUTHOR : © Tisaac
 */
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define(['dojo', 'dojo/_base/declare', g_gamethemeurl + 'modules/js/vendor/nouislider.min.js', 'ebg/core/gamegui'], (
  dojo,
  declare,
  noUiSlider
) => {
  const isPromise = (v) => typeof v === 'object' && typeof v.then === 'function';

  return declare('customgame.game', ebg.core.gamegui, {
    /*
     * Constructor
     */
    constructor() {
      this._notifications = [];
      this._activeStates = [];
      this._connections = [];
      this._selectableNodes = [];
      this._activeStatus = null;
      this._helpMode = false;
      this._dragndropMode = false;
      this._customTooltipIdCounter = 0;
      this._registeredCustomTooltips = {};

      this._notif_uid_to_log_id = {};
      this._notif_uid_to_mobile_log_id = {};
      this._last_notif = null;
      dojo.place('loader_mask', 'overall-content', 'before');
      dojo.style('loader_mask', {
        height: '100vh',
        position: 'fixed',
      });

      this._displayNotifsOnTop = true;
      this._displayNotifsOnTopWhenGameState = true;
      this._hideNotifsWhenMultiActive = false;
      this._displayRestartButtons = true;
      this.alwaysFixTopActions = true;
      //Max percentage of screen to use with top bar :
      this.alwaysFixTopActionsMaximum = 30;
    },




    showMessage(msg, type) {
      if (type == 'error') {
        console.error(msg);
        if (msg && msg.startsWith("!!!")) {
          if (msg == "!!!checkVersion") {
            this.infoDialog(  _("A new version of this game is now available"),_("Reload Required"), () => {window.location.reload(true);},true);
          }
          return; // suppress red banner and gamelog message
        }
      }
      return this.inherited(arguments);
    },

    isFastMode() {
      return this.instantaneousMode;
    },

    setModeInstantaneous() {
      if (this.instantaneousMode == false) {
        this.instantaneousMode = true;
        dojo.style('leftright_page_wrapper', 'display', 'none');
        dojo.style('loader_mask', 'display', 'block');
        dojo.style('loader_mask', 'opacity', 1);
      }
    },

    unsetModeInstantaneous() {
      if (this.instantaneousMode) {
        this.instantaneousMode = false;
        $('leftright_page_wrapper').style.removeProperty('display');
        dojo.style('loader_mask', 'display', 'none');
        this.updateLayout();
      }
    },

    /*
     * [Undocumented] Override BGA framework functions to call onLoadingComplete when loading is done
     */
    setLoader(value, max) {
      this.inherited(arguments);
      if (!this.isLoadingComplete && value >= 100) {
        this.isLoadingComplete = true;
        this.onLoadingComplete();
      }
    },

    onLoadingComplete() {
      debug('Loading complete');
      //      this.cancelLogs(this.gamedatas.canceledNotifIds);
    },


    /*
     * Detect if spectator or replay
     */
    isReadOnly() {
      return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode;
    },

    /*
     * Make an AJAX call with automatic lock
     */
    performAction(action, data, check = true, checkLock = true) {
      debug('performAction()',action, data, check, checkLock );
      if (check && !this.checkAction(action)) return false;
      if (!check && checkLock && !this.checkLock()) return false;

      data = data || {};
      let options = {};
      if (data.lock === undefined) {
        options.lock = true;
      } else if (data.lock === false) {
        delete data.lock;
      }
      delete data.lock;

      data.v = this.gamedatas.version;
      return this.bgaPerformAction(action, data);
    },

    /*
     * onEnteringState:
     * 	this method is called each time we are entering into a new game state.
     *
     * params:
     *  - str stateName : name of the state we are entering
     *  - mixed args : additional information
     */
    onEnteringState(stateName, args) {
      debug('Entering state: ' + stateName, args);
      if (this.isFastMode()) return;
      if (this._activeStates.includes(stateName) && !this.isCurrentPlayerActive()) return;

      // Call appropriate method
      var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },

    /**
     * onLeavingState:
     * 	this method is called each time we are leaving a game state.
     *
     * params:
     *  - str stateName : name of the state we are leaving
     */
    onLeavingState(stateName) {
      debug('Leaving state: ' + stateName);
      if (this.isFastMode()) return;
      //this.clearPossible();

      // Call appropriate method
      var methodName = 'onLeavingState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName]();
    },

/*    removeAllActionButtons() {
      this.removeActionButtons();
      dojo.empty('customActions');
      dojo.empty('restartAction');
    },*/
    
/*    undoToStep(stepId) {
      this.checkAction('actRestart');
      this.performAction('actUndoToStep', { stepId }, false);
    },*/

  /*  clearPreAnimation() {
      debug('clearPreAnimation()' );
      this._connections.forEach(dojo.disconnect);
      this._connections = [];
      this._selectableNodes.forEach((node) => {
        if ($(node)) dojo.removeClass(node, 'selectable selected');
      });
      this._selectableNodes = [];
      dojo.query('.unselectable').removeClass('unselectable');
      dojo.query('.selectable').removeClass('selectable');
      dojo.query('.selected').removeClass('selected');
    },*/

  /*  clearPossible() {
      debug('clearPossible()' );
      this.clearPreAnimation();
      this.removeAllActionButtons(); 
    },


    clearPossible() {
      this.clearTitleBar();

      this._connections.forEach(dojo.disconnect);
      this._connections = [];
      this._selectableNodes.forEach((node) => {
        if ($(node)) dojo.removeClass(node, 'selectable selected');
      });
      this._selectableNodes = [];
      dojo.query('.unselectable').removeClass('unselectable');
      dojo.query('.selected').removeClass('selected');
    },*/


    

    /**
     * Check change of activity
     */
    onUpdateActionButtons(stateName, args) {
      let status = this.isCurrentPlayerActive();
      if (status != this._activeStatus) {
        debug('Update activity: ' + stateName, status);
        this._activeStatus = status;

        // Call appropriate method
        var methodName = 'onUpdateActivity' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
        if (this[methodName] !== undefined) this[methodName](args, status);
      }
    },

    /*
     * setupNotifications
     */
    getVisibleTitleContainer() {
      function isVisible(elem) {
        return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
      }

      if (isVisible($('pagemaintitletext'))) {
        return $('pagemaintitletext');
      } else {
        return $('gameaction_status');
      }
    },


    setupNotifications() {
      console.log("setupNotifications",this._notifications); 
      /*
      this._notifications.forEach((notif) => {
        var functionName = 'notif_' + notif[0];

        let wrapper = (args) => {
          if(this._displayNotifsOnTop 
            && !(this.gamedatas.gamestate.type == 'multipleactiveplayer' && this._hideNotifsWhenMultiActive)
            || this.gamedatas.gamestate.type == 'game' && this._displayNotifsOnTopWhenGameState){
            let msg = this.format_string_recursive(args.log, args.args);
            if (msg != '') {
              $('gameaction_status').innerHTML = msg;
              $('pagemaintitletext').innerHTML = msg;
              this.removeAllActionButtons();
            }
          }
          let timing = this[functionName](args);
          if (timing === undefined) {
            if (notif[1] === undefined) {
              console.error("A notification don't have default timing and didn't send a timing as return value : " + notif[0]);
              return;
            }

            // Override default timing by 1 in case of fast replay mode
            timing = this.isFastMode() ? 0 : notif[1];
          }

          if (timing !== null && !isPromise(timing)) {
            this.notifqueue.setSynchronousDuration(timing);
          }
        };

        dojo.subscribe(notif[0], this, wrapper);
        this.notifqueue.setSynchronous(notif[0]);

        if (notif[2] != undefined) {
          this.notifqueue.setIgnoreNotificationCheck(notif[0], notif[2]);
        }
      });

      this.notifqueue.setSynchronousDuration = (duration) => {
        setTimeout(() => dojo.publish('notifEnd', null), duration);
      };
      */
     
      //2024 NEw Framework function
      this.bgaSetupPromiseNotifications( {
        minDuration: 900, // because slide 800
        //minDurationNoText: 500,
        logger: debug,
        onStart: (notifName, msg, args) => {
          if (this._displayNotifsOnTop && msg != '') {
            $('gameaction_status').innerHTML = msg;
            $('pagemaintitletext').innerHTML = msg;
            //this.removeAllActionButtons();
          }
          //this.clearPreAnimation();
        }, 
        onEnd: (notifName, msg, args) => { 
          //To see log 
        },
      });
    },

    /**
    Wrapper for setting a notification duration which may depends on player prefs/settings
    */
    setNotifDuration(time = 0){
      //if(!this.isSettingAnimationsEnabled()) time = 0;
      if(!this.bgaAnimationsActive()) time = 0;
      this.notifqueue.setSynchronousDuration(time);
    },


    /*
     * Add a timer on an action button :
     * params:
     *  - buttonId : id of the action button
     *  - time : time before auto click
     *  - pref : 0 is disabled (auto-click), 1 if normal timer, 2 if no timer and show normal button
     */

    startActionTimer(buttonId, time, pref, autoclick = false) {
      var button = $(buttonId);
      var isReadOnly = this.isReadOnly();
      if (button == null || isReadOnly || pref == 2) {
        debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly, 'prefValue=' + pref);
        return;
      }

      // If confirm disabled, click on button
      if (pref == 0) {
        if (autoclick) button.click();
        return;
      }

      this._actionTimerLabel = button.innerHTML;
      this._actionTimerSeconds = time;
      this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
          this.stopActionTimer();
        } else if (this._actionTimerSeconds-- > 1) {
          button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } else {
          debug('Timer ' + buttonId + ' execute');
          button.click();
          this.stopActionTimer();
        }
      };
      this._actionTimerFunction();
      this._actionTimerId = window.setInterval(this._actionTimerFunction.bind(this), 1000);
      debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
    },

    stopActionTimer() {
      if (this._actionTimerId != null) {
        debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
      }
    },

    /*
     * Play a given sound that should be first added in the tpl file
     */
    playSound(sound, playNextMoveSound = true) {
      playSound(sound);
      playNextMoveSound && this.disableNextMoveSound();
    },
/*
    resetPageTitle() {
      this.changePageTitle();
    },

    changePageTitle(suffix = null, save = false) {
      if (suffix == null) {
        suffix = 'generic';
      }

      let state = this.gamedatas.gamestate;
      if (state.private_state && this.isCurrentPlayerActive()) {
        state = state.private_state;
        if (!state['descriptionmyturn' + suffix]) return;
        state.descriptionmyturn = state['descriptionmyturn' + suffix];
        this.updatePageTitle(state);
        return;
      }

      if (!this.gamedatas.gamestate['descriptionmyturn' + suffix] && this.isCurrentPlayerActive()) return;

      if (save) {
        this.gamedatas.gamestate.descriptionmyturngeneric = this.gamedatas.gamestate.descriptionmyturn;
        this.gamedatas.gamestate.descriptiongeneric = this.gamedatas.gamestate.description;
      }

      this.gamedatas.gamestate.descriptionmyturn = this.gamedatas.gamestate['descriptionmyturn' + suffix];
      if (this.gamedatas.gamestate['description' + suffix])
        this.gamedatas.gamestate.description = this.gamedatas.gamestate['description' + suffix];
      this.updatePageTitle();
    },*/


    /*
     * Add a blue/grey button if it doesn't already exists
     */
  /*  addPrimaryActionButton(id, text, callback, zone = 'customActions') {
      if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'blue');
    },

    addSecondaryActionButton(id, text, callback, zone = 'customActions') {
      if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'gray');
    },

    addDangerActionButton(id, text, callback, zone = 'customActions') {
      if (!$(id)) this.addActionButton(id, text, callback, zone, false, 'red');
    },*/
/*
    clearActionButtons() {
      dojo.empty('customActions');
    },*/


    /************************
     ******* SETTINGS ********
     ************************/
    isMobile() {
      return $('ebd-body').classList.contains('mobile_version');
    },

  
    getScale(id) {
      let transform = dojo.style(id, 'transform');
      if (transform == 'none') return 1;

      var values = transform.split('(')[1];
      values = values.split(')')[0];
      values = values.split(',');
      let a = values[0];
      let b = values[1];
      return Math.sqrt(a * a + b * b);
    },
    //11/2024 Framework version 
    wait(n) {
      return new Promise((resolve, reject) => {
        if (this.isFastMode()) {
          resolve();
        } else {
          setTimeout(() => resolve(), n);
        }
      });
    },

    slide: async function(mobileElt, targetElt, options = {}) {
      let config = Object.assign(
        {
          duration: 800,
          delay: 0,
          destroy: false,
          attach: true,
          changeParent: true, // Change parent during sliding to avoid zIndex issue
          pos: null,
          className: 'moving',
          from: null,
          clearPos: true,
          beforeBrother: null,
          to: null,

          phantom: true,
        },
        options
      );
      config.phantomStart = config.phantomStart || config.phantom;
      config.phantomEnd = config.phantomEnd || config.phantom;

      // Mobile elt
      mobileElt = $(mobileElt);
      let mobile = mobileElt;
      // Target elt
      targetElt = $(targetElt);
      let targetId = targetElt;
      const newParent = config.attach ? targetId : $(mobile).parentNode;

      // Handle fast mode
      if (!this.bgaAnimationsActive() && (config.destroy || config.clearPos)) {
        if (config.destroy) this.destroy(mobile);
        else dojo.place(mobile, targetElt);

        return new Promise((resolve, reject) => {
          resolve();
        });
      }

      // Handle phantom at start
      if (config.phantomStart && config.from == null) {
        mobile = dojo.clone(mobileElt);
        dojo.attr(mobile, 'id', mobileElt.id + '_animated');
        dojo.place(mobile, 'game_play_area');
        this.placeOnObject(mobile, mobileElt);
        dojo.addClass(mobileElt, 'phantom');
        config.from = mobileElt;
      }

      // Handle phantom at end
      if (config.phantomEnd) {
        targetId = dojo.clone(mobileElt);
        dojo.attr(targetId, 'id', mobileElt.id + '_afterSlide');
        dojo.addClass(targetId, 'phantom');
        if (config.beforeBrother != null) {
          dojo.place(targetId, config.beforeBrother, 'before');
        } else {
          dojo.place(targetId, targetElt);
        }
      }

      dojo.style(mobile, 'zIndex', 5000);
      dojo.addClass(mobile, config.className);
      if (config.changeParent) this.changeParent(mobile, 'game_play_area');
      if (config.from != null) this.placeOnObject(mobile, config.from);
      return new Promise(async (resolve, reject) => {
        const animation =
          config.pos == null
            ? this.slideToObject(mobile, config.to || targetId, config.duration, config.delay)
            : this.slideToObjectPos(mobile, config.to || targetId, config.pos.x, config.pos.y, config.duration, config.delay);

        dojo.connect(animation, 'onEnd', () => {
          dojo.style(mobile, 'zIndex', null);
          dojo.removeClass(mobile, config.className);
          if (config.phantomStart) {
            dojo.place(mobileElt, mobile, 'replace');
            dojo.removeClass(mobileElt, 'phantom');
            mobile = mobileElt;
          }
          if (config.destroy) this.destroy(mobile);
          else if (config.changeParent) {
            if (config.phantomEnd) dojo.place(mobile, targetId, 'replace');
            else this.changeParent(mobile, newParent);
          }
          if (config.clearPos && !config.destroy) dojo.style(mobile, { top: null, left: null, position: null });
          resolve();
        });
        //animation.play();
        //await this.bgaPlayDojoAnimation(animation);
        this.bgaPlayDojoAnimation(animation);
      });
    },

    changeParent(mobile, new_parent, relation) {
      if (mobile === null) {
        console.error('attachToNewParent: mobile obj is null');
        return;
      }
      if (new_parent === null) {
        console.error('attachToNewParent: new_parent is null');
        return;
      }
      if (typeof mobile == 'string') {
        mobile = $(mobile);
      }
      if (typeof new_parent == 'string') {
        new_parent = $(new_parent);
      }
      if (typeof relation == 'undefined') {
        relation = 'last';
      }
      var src = this.getBoundingClientRectIgnoreZoom(mobile);
      dojo.style(mobile, 'position', 'absolute');
      dojo.place(mobile, new_parent, relation);
      var tgt = this.getBoundingClientRectIgnoreZoom(mobile);
      var box = dojo.marginBox(mobile);
      var cbox = dojo.contentBox(mobile);
      var left = box.l + src.x - tgt.x;
      var top = box.t + src.y - tgt.y;
      this.positionObjectDirectly(mobile, left, top);
      box.l += box.w - cbox.w;
      box.t += box.h - cbox.h;
      return box;
    },

    positionObjectDirectly(mobileObj, x, y) {
      // do not remove this "dead" code some-how it makes difference
      dojo.style(mobileObj, 'left'); // bug? re-compute style
      // console.log("place " + x + "," + y);
      dojo.style(mobileObj, {
        left: x + 'px',
        top: y + 'px',
      });
      dojo.style(mobileObj, 'left'); // bug? re-compute style
    },

    /*
     * Wrap a node inside a flip container to trigger a flip animation before replacing with another node
     */
    flipAndReplace: async function(target, newNode, duration = 1000) {
      debug("flipAndReplace start");
      if (!this.bgaAnimationsActive()) {
        dojo.place(newNode, target, 'replace');
        return;
      }

      return new Promise((resolve, reject) => {
        // Wrap everything inside a flip container
        let container = dojo.place(
          `<div class="flip-container flipped">
            <div class="flip-inner">
              <div class="flip-front"></div>
              <div class="flip-back"></div>
            </div>
          </div>`,
          target,
          'after'
        );
        dojo.place(target, container.querySelector('.flip-back'));
        dojo.place(newNode, container.querySelector('.flip-front'));

        // Trigget flip animation
        container.offsetWidth;
        dojo.removeClass(container, 'flipped');

        // Clean everything once it's done
        setTimeout(() => {
          dojo.place(newNode, container, 'replace');
          resolve();
        }, duration);
      });
    },


    /* Helper to work with local storage */
    getConfig(value, v) {
      return localStorage.getItem(value) == null || isNaN(localStorage.getItem(value)) ? v : localStorage.getItem(value);
    },


    /**
     * Own counter implementation that works with replay
     */
    createCounter(id, defaultValue = 0, linked = null) {
      if (!$(id)) {
        console.error('Counter : element does not exist', id);
        return null;
      }

      let game = this;
      let o = {
        span: $(id),
        linked: linked ? $(linked) : null,
        targetValue: 0,
        currentValue: 0,
        speed: 100,
        getValue() {
          return this.targetValue;
        },
        setValue(n) {
          this.currentValue = +n;
          this.targetValue = +n;
          this.span.innerHTML = +n;
          this.span.dataset.counter = +n;
          if(this.currentValue==0) this.span.parentNode.classList.add("counter_empty");
          else this.span.parentNode.classList.remove("counter_empty");
          if (this.linked) this.linked.innerHTML = +n;
        },
        toValue(n) {
          if (!game.bgaAnimationsActive()) {
            this.setValue(n);
            return;
          }

          this.targetValue = +n;
          if (this.currentValue != n) {
            this.span.classList.add('counter_in_progress');
            setTimeout(() => this.makeCounterProgress(), this.speed);
          }
        },
        goTo(n, anim) {
          if (anim) this.toValue(n);
          else this.setValue(n);
        },
        incValue(n) {
          let m = +n;
          this.toValue(this.targetValue + m);
        },
        makeCounterProgress() {
          if (this.currentValue == this.targetValue) {
            setTimeout(() => this.span.classList.remove('counter_in_progress'), this.speed);
            return;
          }

          let step = Math.ceil(Math.abs(this.targetValue - this.currentValue) / 5);
          this.currentValue += (this.currentValue < this.targetValue ? 1 : -1) * step;
          this.span.innerHTML = this.currentValue;
          this.span.dataset.counter = this.currentValue;
          if(this.currentValue==0) this.span.parentNode.classList.add("counter_empty");
          else this.span.parentNode.classList.remove("counter_empty");
          if (this.linked) this.linked.innerHTML = this.currentValue;
          setTimeout(() => this.makeCounterProgress(), this.speed);
        },
      };
      o.setValue(defaultValue);
      return o;
    },


    /****************
     ***** UTILS *****
     ****************/

    strReplace(str, subst) {
      return dojo.string.substitute(str, subst);
    },



    translate(t) {
      if (typeof t === 'object') {
        return this.format_string_recursive(_(t.log), t.args);
      } else {
        return this.format_string_recursive(_(t), {});
      }
    },

    fsr(log, args) {
      return this.format_string_recursive(log, args);
    },


    //Taken from thoun Ancient Knowledge : reduce a div text size to match a specific zone (on a card for example)
    //EXAMPLE <span class='A'><div class='reduceToFit'>TEST abcdef</div></span> where .A elements define a width
    reduceToFit(element) {
      var div = element; //element.getElementsByTagName("div")[0];
      if (div) {
        var n = window.getComputedStyle(div).fontSize.match(/\d+/);
        if (n)
          for (var a = Number(n[0]); div.clientHeight > element.parentNode.clientHeight && a > 5;) {
            a--;
            div.style.fontSize = "".concat(a, "px")
          }
      }
    },
    reduceTextSizeOnCardElements(cardDiv) {
      if(!cardDiv) return;
      cardDiv.querySelectorAll(".reduceToFit").forEach((e) => {
        this.reduceToFit(e);
      });
    },


  });
});

//FOR STUDIO ONLY //# sourceURL=game.js