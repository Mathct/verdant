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
      
    },


    isFastMode() {
      return this.instantaneousMode;
    },

    /*
     * Detect if spectator or replay
     */
    isReadOnly() {
      return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode;
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

      // Call appropriate method
      var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },


    /**
     * Check change of activity
     */
    onUpdateActionButtons(stateName, args) {

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
      if (this.isFastMode()) return;
      //this.clearPossible();

      // Call appropriate method
      var methodName = 'onLeavingState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName]();
    },


    

 

    /*
     * setupNotifications
     */



    setupNotifications() {
      //console.log("setupNotifications",this._notifications); 
     
      //2024 NEw Framework function
      this.bgaSetupPromiseNotifications( {
        minDuration: 900, // because slide 800
        //minDurationNoText: 500,
        logger: debug,
        /*onStart: (notifName, msg, args) => {
          if (this._displayNotifsOnTop && msg != '') {
            $('gameaction_status').innerHTML = msg;
            $('pagemaintitletext').innerHTML = msg;
            //this.removeAllActionButtons();
          }
          //this.clearPreAnimation();
        }, 
        onEnd: (notifName, msg, args) => { 
          //To see log 
        },*/
      });
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



    /************************
     ******* SETTINGS ********
     ************************/

  
    
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






    /****************
     ***** UTILS *****
     ****************/




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