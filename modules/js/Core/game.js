/**
 * BGA framework override
 * AUTHOR : © Tisaac
 */
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui'], (dojo, declare) => {
  return declare('customgame.game', [ebg.core.gamegui], {
    /*
     * Constructor
     */
    constructor() {
 


      dojo.place('loader_mask', 'overall-content', 'before');
      dojo.style('loader_mask', {
        height: '100vh',
        position: 'fixed',
      });
    },


    isFastMode() {
      return this.instantaneousMode;
    },

 /*   setModeInstantaneous() {
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
    },*/

    unsetModeInstantaneous() {
      this.inherited(arguments);
      this.updateLayout();
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
      if (this.isFastMode()) 
        return;

      // Call appropriate method
      var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },


    /**
     * Check change of activity
     */
    onUpdateActionButtons(stateName, args) {
        let status = this.isCurrentPlayerActive();
        // Call appropriate method
        var methodName = 'onUpdateActivity' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
        if (this[methodName] !== undefined) this[methodName](args, status);
      //}
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
      if (this.isFastMode()) 
        return;
      //this.clearPossible();

      // Call appropriate method
      var methodName = 'onLeavingState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName]();
    },


    

 

    /*
     * setupNotifications
     */


    setupNotifications() {
      console.log("setupNotifications"); 
     
      //2024 NEw Framework function
      this.bgaSetupPromiseNotifications( {
        minDuration: 900, // because slide 800
        logger: debug
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
     */

    startActionTimer(buttonId, time) {
      var button = $(buttonId);
      var isReadOnly = this.isReadOnly();
      if (button === null || isReadOnly) {
        debug('Ignoring startActionTimer(' + buttonId + ')', 'readOnly=' + isReadOnly);
        return;
      }

      this._actionTimerLabel = button.innerHTML;
      this._actionTimerSeconds = time;
      this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button === null) {
          this.stopActionTimer();
        } else if (this._actionTimerSeconds-- > 1) {
          button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } else {
          debug('Timer ' + buttonId + ' execute');
          button.click();
        }
      };
      dojo.connect($(buttonId), 'click', () => this.stopActionTimer());
      this._actionTimerFunction();
      this._actionTimerId = window.setInterval(this._actionTimerFunction, 1000);
      debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
    },

    stopActionTimer(buttonWithTimer = null) {
      if (this._actionTimerId != null) {
        debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
      }
      if (buttonWithTimer) {
        $(buttonWithTimer).innerHTML = this._actionTimerLabel;
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
          phantom: false,
          targetPos: 'last',
         
        },
        options
      );
      config.phantomStart = config.phantomStart || config.phantom;
      config.phantomEnd = config.phantomEnd || config.phantom;

      // Handle phantom at start
      mobileElt = $(mobileElt);
      let mobile = mobileElt;
      if (config.phantomStart) {
        mobile = dojo.clone(mobileElt);
        dojo.attr(mobile, 'id', mobileElt.id + '_animated');
        dojo.place(mobile, 'game_play_area');
        this.placeOnObject(mobile, mobileElt);
        dojo.addClass(mobileElt, 'phantom');
        config.from = mobileElt;
      }

      // Handle phantom at end
      targetElt = $(targetElt);
      let targetId = targetElt;
      if (config.phantomEnd) {
        targetId = dojo.clone(mobileElt);
        dojo.attr(targetId, 'id', mobileElt.id + '_afterSlide');
        dojo.addClass(targetId, 'phantom');
        dojo.place(targetId, targetElt, config.targetPos);
      }
      // Handle fast mode
      if (!this.bgaAnimationsActive() && (config.destroy || config.clearPos)) {
        if (config.destroy) this.destroy(mobile);
        else dojo.place(mobile, targetElt);

        return new Promise((resolve, reject) => {
          resolve();
        });
      }

      const newParent = config.attach ? targetId : $(mobile).parentNode;
      dojo.style(mobile, 'zIndex', 1000);
      dojo.addClass(mobile, config.className);
      if (config.changeParent) this.changeParent(mobile, 'game_play_area');
      if (config.from != null) this.placeOnObject(mobile, config.from);
      return new Promise(async (resolve, _) => {
        const animation =
          config.pos == null
            ? this.slideToObject(mobile, targetId, config.duration, config.delay)
            : this.slideToObjectPos(mobile, targetId, config.pos.x, config.pos.y, config.duration, config.delay);

        dojo.connect(animation, 'onEnd', () => {
          dojo.style(mobile, 'zIndex', null);
          dojo.removeClass(mobile, config.className);
          if (config.phantomStart) {
            dojo.place(mobileElt, mobile, 'replace');
            dojo.removeClass(mobileElt, 'phantom');
            mobile = mobileElt;
          }
          if (config.changeParent) {
            if (config.phantomEnd) dojo.place(mobile, targetId, 'replace');
            else this.changeParent(mobile, newParent);
          }
          if (config.destroy) this.destroy(mobile);
          if (config.clearPos && !config.destroy) 
            dojo.style(mobile, { top: null, left: null, position: null });
          resolve();
        });
        animation.play();
        //await this.bgaPlayDojoAnimation(animation);
        //this.bgaPlayDojoAnimation(animation);
      });
    },

    changeParent(mobile, new_parent, clearStyles = false) {
      if (mobile === null) {
        console.error('attachToNewParent: mobile obj is null');
        return;
      }
      if (new_parent === null) {
        console.error('attachToNewParent: new_parent is null');
        return;
      }
      if (typeof mobile === 'string') {
        mobile = $(mobile);
      }
      if (typeof new_parent === 'string') {
        new_parent = $(new_parent);
      }
      var src = dojo.position(mobile);
      dojo.style(mobile, 'position', 'absolute');
      dojo.place(mobile, new_parent, 'last');
      var tgt = dojo.position(mobile);
      var box = dojo.marginBox(mobile);
      var cbox = dojo.contentBox(mobile);
      var left = box.l + src.x - tgt.x;
      var top = box.t + src.y - tgt.y;
      this.positionObjectDirectly(mobile, left, top);
      box.l += box.w - cbox.w;
      box.t += box.h - cbox.h;
      if (clearStyles) {
        dojo.style(mobile, {
          top: null,
          left: null,
          position: null,
        });
      }
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



    /****************
     ***** UTILS *****
     ****************/

    strReplace(str, subst) {
      return dojo.string.substitute(str, subst);
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