/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * verdant implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com> && <Yannick Priol> <camertwo@hotmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * verdant.js
 *
 * verdant user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */


 //Tisaac way to debug ;)
var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};


define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    g_gamethemeurl + 'modules/js/Core/game.js'
],
function (dojo, declare) {

    /* CONSTANTS HERE */
    const TOOLTIP_DELAY = 500;
    const ROWS = 3;
    const COLS = 5;
    const HOUSE_PADDING = 13;
    const TABLE_WIDTH = 1200;
    const TABLE_HEIGHT = 800;

    const NORMAL = 1;
    const ADVANCED = 2;
    const EXPANDED = 3;




    return declare("bgagame.verdant", [customgame.game], {
        constructor: function() {
            console.log('verdant constructor');
        },

            
 /////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
setup: function( gamedatas )
{
    console.log( "Starting game setup" );

               
    // arrays from gamedatas

    this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)
    this.players_ordered = gamedatas.players_ordered;


    this.possibleCards = [];

    this.thumb_counter = [];

    // multi
    this.pot_counter = [];
    //solo
    this.pot_deck_counter = [];
    this.pot_discard_counter = [];

    this.verdancy_counter = [];
    this.plant_deck_counter = gamedatas.plants_deck;
    this.room_deck_counter = gamedatas.rooms_deck;

    this.timer = 5;
    //let timerPreference = this.getGameUserPreference(101);
    if (this.getGameUserPreference(101) !== null && this.getGameUserPreference(101) !== undefined) {
        this.timer = this.getGameUserPreference(101);
    }
 

    this.setupPlayersBoard();
    this.setupBoard();
    this.setupCounters();
    this.setupTooltips();



   this.setupNotifications();

   // THOUN
   this.addHelp();

    

    console.log( "Ending game setup" );
},

/////////////////////////////////////////////////////////////////////////////////   
//         _____ _        _            
//        / ____| |      | |           
//       | (___ | |_ __ _| |_ ___  ___ 
//        \___ \| __/ _` | __/ _ \/ __|
//        ____) | || (_| | ||  __/\__ \
//       |_____/ \__\__,_|\__\___||___/
//                                    
/////////////////////////////////////////////////////////////////////////////////    


///////////////////////////////////////////////////
//// Game & client states

// onEnteringState: this method is called each time we are entering into a new game state.
//                  You can use this method to perform some user interface changes at this moment.
//
onEnteringState: function( stateName, args )
{
    if( stateName != 'pending') {
        console.log('Entering state: '+stateName, args);
    }
      
    
    switch( stateName )
    {

        case 'playerTurn':
            this.args = args.args;

             this.possibles = [];

            if(this.isCurrentPlayerActive()) {
                this.args.selectable.forEach(sid => {
                    if (sid.startsWith("grid_")) {
                       console.log( this.args.card);
                        // possible positions are created 
                        this.addPossibleCardPositions(sid,this.args.card[0]);
                    }
                    else {
                        if (sid.startsWith("icon_thumb_")) {
                            const element = document.getElementById(sid);
                            this.addSVGs(element,"selectable");
                            
                        }
                        else {
                            dojo.addClass(sid,"selectable");
                        }
                        
                        this.possibles.push(sid);
                    }
                    
                });

                this.args.selected.forEach(sid => {

                    if (sid.startsWith("icon_thumb_")) {
                        const element = document.getElementById(sid);
                        this.addSVGs(element,"selected");
                            
                        }
                        else {
                            dojo.addClass(sid,"selected");
                        }  
                });

                if (this.args.selectablemulti) {
                    this.args.selectablemulti.forEach(sid => {
                        dojo.addClass(sid,"selectablemulti");
                        this.possibles.push(sid);  
                    });
                }

                if (this.args.selectablethumb) {
                    this.args.selectablethumb.forEach(sid => {
                        dojo.addClass(sid, "selectablethumb");
                        this.possibles.push(sid);
                    });
                }

                console.log( 'possibles', this.possibles);
                // connections are made once all elements have been created
                this.setupConnections(this.possibles);

                if(args.args.titleyou != null) {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                }
            
            }
            else{
                if(args.args.title != null) {
                    $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                }
            }
            break;



        case 'playerTurnMulti':
            this.args = args.args;

            
            this.possibles = [];
    
            if(this.isCurrentPlayerActive())
            {
                const player_id = this.getCurrentPlayerId()

                this.args.selectable[player_id].forEach(sid => {
                    // possible positions are created 
                    this.addPossibleCardPositions(sid,this.args.card[player_id][0]);
                });

                console.log( 'possibles', this.possibles);
                // connections are made once all elements have been created
                this.setupConnections(this.possibles);
    
            }
        break;    
    
        case 'dummmy':
            break;
    }
},

// onLeavingState: this method is called each time we are leaving a game state.
//                 You can use this method to perform some user interface changes at this moment.
//
onLeavingState: function( stateName ) {
    
    if( stateName != 'pending') {
        console.log( 'Leaving state: '+stateName );
    }    
    


    dojo.query(".selectable").removeClass("selectable");
    dojo.query(".selected").removeClass("selected");
    dojo.query(".selectablemulti").removeClass("selectablemulti");
    dojo.query(".selectedmulti").removeClass("selectedmulti");
    dojo.query(".selectablethumb").removeClass("selectablethumb");
    dojo.query(".selectedthumb").removeClass("selectedthumb");
    dojo.query(".selectable_thumb_pannel").removeClass("selectable_thumb_pannel");
    this.removeSVGs();

    // Récupérer tous les éléments ayant la classe 'possible_in_house'
    const possibleElements = document.querySelectorAll('.possible_in_house');
    // Supprimer le container parent
    possibleElements.forEach(element => {
        element.parentNode?.remove();
    });
    
    switch( stateName )
    {
        case 'playerTurn':
            this.removeConnections();            
            break;     
    
        case 'dummy':
            break;
    }               
}, 

// onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
//                        action status bar (ie: the HTML links in the status bar).
//        
onUpdateActionButtons: function( stateName, args ) {

    if( stateName != 'pending') {
        console.log( 'onUpdateActionButtons: '+stateName, args );
    } 
   
              
    if( this.isCurrentPlayerActive() ) {            
        switch( stateName )
        {
            case "playerTurn":
                for( var nb in args.buttons )
                { 
                    if(args.buttons[nb] == "cancel") {
                        this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "pass") {
                        this.addActionButton( 'pass', _("End Turn") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "reset") {
                        this.addActionButton( 'reset', _("Reset selections") ,'onOpResetSelection', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "store") {
                        this.addActionButton( 'store', _("Store") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "yes") {
                        this.addActionButton( 'yes', _("Yes") ,'onOpButton', null, null, 'blue' );
                        this.startActionTimer('yes', this.timer, 1);
                    }
                    if(args.buttons[nb] == "no") {
                        this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                    }
                    if(args.buttons[nb] == "confirm") {
                        this.addActionButton( 'confirm', _("Confirm") ,'onOpButton', null, null, 'blue' );
                    }
                    if(args.buttons[nb] == "thumb") {
                        this.addActionButton( 'thumb', `<div class="thumb_bt"`,'onOpButton', null, null, 'none' );
                   }
                    if(args.buttons[nb].startsWith("tilebt"))
                    {
                        const explode = args.buttons[nb].split("_");

                        const x =  explode[1] % 10 - 1;
                        const y = Math.floor(explode[1] / 10) - 1;

                        let item_bt_css; 
                        if( y == 5) {
                            const tool_y = x;
                            item_bt_css = `<div class="item_bt" style="background-position: -900% -${tool_y}00% ;"></div>`;
                        }
                        else {
                            item_bt_css = `<div class="item_bt" style="background-position: -${x}00% -${y}00% ;"></div>`;
                        }
                        this.addActionButton(args.buttons[nb], item_bt_css, 'onOpButton', null, null, 'none');
                    }

                    if(args.buttons[nb] == "validatemulti_handtrowel")
                    {
                        this.addActionButton( 'validatemulti_handtrowel', _("Validate selection") ,'onOpValidatemulti_Handtrowel', null, null, 'blue' );
                        dojo.addClass( 'validatemulti_handtrowel', 'disabled');
                                
                    }
                    if(args.buttons[nb] == "validate_verdancy")
                    {
                        this.addActionButton( 'validate_verdancy', _("Add verdancy") ,'onOpValidate_Thumb', null, null, 'blue' );
                        dojo.addClass( 'validate_verdancy', 'disabled');
                    }
                    if(args.buttons[nb] == "validate_cards")
                    {
                        this.addActionButton( 'validate_cards', _("Reset cards") ,'onOpValidate_Thumb', null, null, 'blue' );
                        dojo.addClass( 'validate_cards', 'disabled');
                    }                    
                    if(args.buttons[nb] == "validate_tokens")
                    {
                        this.addActionButton( 'validate_tokens', _("Reset tokens") ,'onOpValidate_Thumb', null, null, 'blue' );
                        dojo.addClass( 'validate_tokens', 'disabled');
                    }
                    if(args.buttons[nb] == "validate_mixed")
                    {
                        this.addActionButton( 'validate_mixed', _("Mixed selection") ,'onOpValidate_Thumb', null, null, 'blue' );
                        dojo.addClass( 'validate_mixed', 'disabled');
                    }

                }
                break;
        }
    }
},        



/////////////////////////////////////////////////////////////////////////////////         
//   _    _ _   _ _ _ _                          _   _               _     
//  | |  | | | (_) (_) |                        | | | |             | |    
//  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___ 
//  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
//  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
//   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
//                         __/ |                                           
//                        |___/                                            
/////////////////////////////////////////////////////////////////////////////////  

divYou : function() {
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;
           
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},


removeSVGs: function() {
    // Sélectionner tous les éléments <svg> dans le document
    const svgs = document.querySelectorAll('svg');
   
    // Parcourir chaque <svg>
    svgs.forEach(svg => {
    // Vérifier si le <svg> contient un <path> avec la classe 'path_selectable' ou 'path_selected'
    const path1 = svg.querySelector('path.path_selectable');
    const path2 = svg.querySelector('path.path_selected');
    if (path1 || path2) {
    // Supprimer le <svg> du DOM
    svg.remove();
    }
   
    });
},

addSVGs: function(image, type) {
    // Créer un SVG avec le path pour le contour
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", "0 0 200 200"); // Dimensions originales du PNG
    
     // Créer un path pour un contour
    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    /* ICI, en rouge, c’est le PATH que l’on retrouve en bas de fichier SVG*/
    path.setAttribute("d", "m 105.0034,174.96362 c -6.770681,-0.80075 -15.634114,-2.57047 -19.69652,-3.9327 -4.860669,-1.62992 -10.921923,-2.72351 -17.726866,-3.19833 -8.247341,-0.57549 -11.350141,-1.23671 -15.327288,-3.2663 -12.370853,-6.313 -20.395061,-22.23693 -20.395061,-40.47367 0,-21.92022 10.40745,-36.209191 27.28004,-37.454281 3.289135,-0.24272 6.969321,-0.641673 8.178192,-0.886572 2.819798,-0.571248 5.188247,-5.146885 5.188247,-10.023232 0,-6.079248 3.569251,-18.344131 7.569827,-26.011915 12.023957,-23.045956 38.422549,-32.241214 61.032479,-21.259064 7.05219,3.425408 15.65847,11.569753 18.75611,17.749405 5.12223,10.218577 7.13473,24.804033 5.23987,37.975325 -1.11434,7.745775 -1.07004,8.983113 0.4248,11.863151 3.75653,7.237643 3.46696,17.915383 -1.06659,39.331073 -1.82146,8.60419 -6.81191,24.15861 -8.65729,26.9832 -2.90134,4.44101 -9.25193,9.47851 -14.34044,11.37538 -7.14783,2.66451 -20.51044,3.11476 -36.45951,1.22853 z");
    
     if(type == "selectable")
     {
     // Ajouter la classe 'selectable' au div d'image
     image.classList.add('selectable_thumb_pannel');
     // Ajouter la classe 'selectable' au path (contour jaune)
     path.setAttribute("class", "path_selectable");
     }
     if(type == "selected")
     {
     // Ajouter la classe 'selected' au div d'image
     //image.classList.add('selected_thumb_pannel');
     // Ajouter la classe 'selectable' au path (contour pointillé jaune)
     path.setAttribute("class", "path_selected");
     }
     // Ajouter le path au SVG
     svg.appendChild(path);
    
     // Ajouter le SVG en tant qu'élément enfant du div d'image
     image.appendChild(svg);
},






/*************************************************
 * 
 *  setup connections from this.args.selectable
 * on each beginning of new State (Player Turn)
 * 
 ************************************************/

setupConnections: function(selectables) {
    this.connections = [];

    selectables.forEach(elt_id => {
        const element = document.getElementById(elt_id);

        //console.log( 'con_elt', element);

        const resourceClickHandler = (evt) => this.onSelect(evt);
        element.addEventListener('click', resourceClickHandler);
        this.connections.push({ element, event: 'click', handler: resourceClickHandler });
    });

},


/*************************************************
 * 
 *  reset all connections 
 *  on leaving a State
 * 
 ************************************************/

removeConnections: function() {
    this.connections.forEach(connection => {
        const { element, event, handler } = connection;
        element.removeEventListener(event, handler);
    });
    this.connections = [];
},



/////////////////////////////////////////////////////////////////////////////////  
//         _____  _                       _                  _   _             
//        |  __ \| |                     ( )                | | (_)            
//        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __  
//        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \ 
//        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
//        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
//                         __/ |                                               
//                        |___/                                                
/////////////////////////////////////////////////////////////////////////////////  

     
stopEvent:function (evt) {
    if (evt) {
        evt.preventDefault();
        evt.stopPropagation();
    }
},

onSelect: function(evt) {        	 
    // Preventing default browser reaction
  
    this.stopEvent( evt );

    if(this.isCurrentPlayerActive() && !this._helpMode) {
        if((evt.currentTarget.classList.contains('selectable')) || (evt.currentTarget.classList.contains('selectable_thumb_pannel'))) {
        
            console.log( 'actSelect', evt.currentTarget.id);
            this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
        }
        else if (evt.currentTarget.classList.contains('selectablethumb')) {
            console.log('currentTarget', evt.currentTarget.id);
            
            if (!evt.currentTarget.closest('[id^="market_cell_"]')) {
                document.querySelectorAll('.selectedthumb').forEach(el => {
                    el.classList.replace('selectedthumb', 'selectablethumb');
                });
                
                const elementId = "#" + evt.currentTarget.id;
                dojo.query(elementId).removeClass("selectablethumb");
                setTimeout(function() {
                    dojo.query(elementId).addClass("selectedthumb");
                }, 10);
                
                dojo.addClass('validate_cards', 'disabled');
                dojo.addClass('validate_tokens', 'disabled');
                dojo.addClass('validate_mixed', 'disabled');
                dojo.removeClass('validate_verdancy', 'disabled');
            } else {
                dojo.query("#" + evt.currentTarget.id).removeClass("selectablethumb");
                dojo.query("#" + evt.currentTarget.id).addClass("selectedthumb");
                
                document.querySelectorAll('.selectedthumb').forEach(el => {
                    if (!el.closest('[id^="market_cell_"]')) {
                        el.classList.remove('selectedthumb');
                        el.classList.add('selectablethumb');
                    }
                });
                
                const elements = Array.from(document.querySelectorAll('.selectedthumb')).filter(el => el.closest('[id^="market_cell_"]'));
                
                const plantMarketCount = elements.filter(el => el.classList.contains('plant')).length;
                const roomMarketCount = elements.filter(el => el.classList.contains('room')).length;
                const tileMarketCount = elements.filter(el => el.classList.contains('tile')).length;
                
                const plantMarketNoChildCount = elements.filter(el => el.classList.contains('plant') && el.children.length === 0).length;
                const plantMarketChildCount = elements.filter(el => el.classList.contains('plant') && el.children.length > 0).length;
                const roomMarketNoChildCount = elements.filter(el => el.classList.contains('room') && el.children.length === 0).length;
                const roomMarketChildCount = elements.filter(el => el.classList.contains('room') && el.children.length > 0).length;
                
                if (tileMarketCount == 1 && ((plantMarketCount == 1) ^ (roomMarketCount == 1))) {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.removeClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else if (tileMarketCount > 0 && plantMarketCount == 0 && roomMarketCount == 0) {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.removeClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else if (tileMarketCount == 0 && plantMarketChildCount == 0 && roomMarketChildCount == 0
                    && (plantMarketNoChildCount > 0 || roomMarketNoChildCount > 0)) {
                    dojo.removeClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                }
            }
        } else if (evt.currentTarget.classList.contains('selectedthumb')) {
            if (!evt.currentTarget.closest('[id^="market_cell_"]')) {
                dojo.query("#" + evt.currentTarget.id).removeClass("selectedthumb");
                dojo.query("#" + evt.currentTarget.id).addClass("selectablethumb");
                dojo.addClass('validate_verdancy', 'disabled');
            } else {
                dojo.query("#" + evt.currentTarget.id).removeClass("selectedthumb");
                dojo.query("#" + evt.currentTarget.id).addClass("selectablethumb");
                
                document.querySelectorAll('.selectedthumb').forEach(el => {
                    if (!el.closest('[id^="market_cell_"]')) {
                        el.classList.remove('selectedthumb');
                        el.classList.add('selectablethumb');
                    }
                });
                
                const elements = Array.from(document.querySelectorAll('.selectedthumb')).filter(el => el.closest('[id^="market_cell_"]'));
                
                const plantMarketCount = elements.filter(el => el.classList.contains('plant')).length;
                const roomMarketCount = elements.filter(el => el.classList.contains('room')).length;
                const tileMarketCount = elements.filter(el => el.classList.contains('tile')).length;

                const plantMarketNoChildCount = elements.filter(el => el.classList.contains('plant') && el.children.length === 0).length;
                const plantMarketChildCount = elements.filter(el => el.classList.contains('plant') && el.children.length > 0).length;
                const roomMarketNoChildCount = elements.filter(el => el.classList.contains('room') && el.children.length === 0).length;
                const roomMarketChildCount = elements.filter(el => el.classList.contains('room') && el.children.length > 0).length;
                
                if (tileMarketCount == 1 && ((plantMarketCount == 1) ^ (roomMarketCount == 1))) {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.removeClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else if (tileMarketCount > 0 && plantMarketCount == 0 && roomMarketCount == 0) {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.removeClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else if (tileMarketCount == 0 && plantMarketChildCount == 0 && roomMarketChildCount == 0
                    && (plantMarketNoChildCount > 0 || roomMarketNoChildCount > 0)) {
                    dojo.removeClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                } else {
                    dojo.addClass('validate_cards', 'disabled');
                    dojo.addClass('validate_tokens', 'disabled');
                    dojo.addClass('validate_mixed', 'disabled');
                    dojo.addClass('validate_verdancy', 'disabled');
                }
            }
        }
        else if(evt.currentTarget.classList.contains('selectablemulti'))
        {
            const elementId = "#" + evt.currentTarget.id;

            dojo.query(elementId).removeClass("selectablemulti");
            setTimeout(function() {
            dojo.query(elementId).addClass("selectedmulti");
            }, 10);

            setTimeout(function() {
            var elements = document.querySelectorAll('.selectedmulti');
            var nombreElements = elements.length;
            var boutonvalidate = document.getElementById('validatemulti_handtrowel');
            if (boutonvalidate !== null)
            {
                if(nombreElements>=1 && nombreElements<=3)
                {
                    dojo.removeClass( 'validatemulti_handtrowel', 'disabled');
                }
                else
                {
                    dojo.addClass( 'validatemulti_handtrowel', 'disabled');
                }
            }
            }, 50);
           
        }

        else if( evt.currentTarget.classList.contains('selectedmulti')) 
        {
            const elementId = "#" + evt.currentTarget.id;

            dojo.query(elementId).removeClass("selectedmulti");
            setTimeout(function() {
            dojo.query(elementId).addClass("selectablemulti");
            }, 10);
         
            setTimeout(function() {
            var elements = document.querySelectorAll('.selectedmulti');
            var nombreElements = elements.length;
            var boutonvalidate = document.getElementById('validatemulti_handtrowel');
            if (boutonvalidate !== null)
            {
                if(nombreElements>=1 && nombreElements<=3)
                {
                    dojo.removeClass( 'validatemulti_handtrowel', 'disabled');
                }
                else
                {
                    dojo.addClass( 'validatemulti_handtrowel', 'disabled');
                }
            }
            }, 50);

            
        }
    }
         

},



onOpButton: function(evt) {
    // Preventing default browser reaction
    this.stopEvent( evt );
  
    this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
    
},

onOpValidatemulti_Handtrowel: function(evt) {

    // Preventing default browser reaction
    this.stopEvent( evt );

    

     // Sélectionnez tous les éléments avec la classe spécifiée
     const elementsAvecClasse = document.querySelectorAll(".selectedmulti");

     // Convertissez la NodeList en un tableau et extrayez les IDs
     const ids = Array.from(elementsAvecClasse, element => element.id);

     let result = "";

     ids.forEach(function(id) {
        const parts = id.split("_"); // Split l'ID avec '_'
        result += (result ? "_" : "") + parts[1]; // Ajoute _ sauf pour le premier élément
    
    });

  
    this.bgaPerformAction('actValidatemultiHandtrowel', { arg1: result});
    
},

onOpValidate_Thumb: function(evt) {
    // Preventing default browser reaction
    this.stopEvent( evt );


    let action = evt.target.id;

    const selectedElements = document.querySelectorAll(".selectedthumb");
    let result = Array.from(selectedElements)
        .map(el => el.id)  // Récupère l'id de chaque élément
        .join(';');  

    // renvoie l'action déclenchée et la liste des id sélectionnés, séparés par ';'.
    this.bgaPerformAction('actValidateThumb', { arg1: `${action};${result}`});
},

onOpResetSelection: function(evt) {
    // Preventing default browser reaction
    this.stopEvent( evt );

    
    document.querySelectorAll('.selectedthumb').forEach(el => {
        el.classList.replace('selectedthumb', 'selectablethumb');
    });

    dojo.addClass('validate_cards', 'disabled');
    dojo.addClass('validate_tokens', 'disabled');
    dojo.addClass('validate_mixed', 'disabled');
    dojo.addClass('validate_verdancy', 'disabled');

},

onGameUserPreferenceChanged: function(pref_id, pref_value) {
    switch (pref_id) {
        case 101: 
        this.timer = pref_value;
        break;
    }
},


/*  ____  _                             ____                  _  */
/* |  _ \| | __ _ _   _  ___ _ __ ___  |  _ \ __ _ _ __   ___| | */
/* | |_) | |/ _` | | | |/ _ \ '__/ __| | |_) / _` | '_ \ / _ \ | */
/* |  __/| | (_| | |_| |  __/ |  \__ \ |  __/ (_| | | | |  __/ | */
/* |_|   |_|\__,_|\__, |\___|_|  |___/ |_|   \__,_|_| |_|\___|_| */
/*                |___/                                          */

setupPlayersBoard: function() {
    console.log('Setting up the players board');

    // on ajoute un conteneur avec la réserve et les pouces (+ compteur)

    Object.values(this.players).forEach((player) => {
        const playerBoardElement = document.getElementById("player_board_" + player.id);
        playerBoardElement.insertAdjacentHTML("beforeend", `<div class="a-board" id="ai_board_${player.id}"></div>`);

        const aiBoard = document.getElementById("ai_board_" + player.id);


        // reserve

        const reserveGroup = `
            <div class="icon-group">
                <div class="icon reserve" id="icon_reserve_${player.id}"></div>
            </div>
        `;
        aiBoard.insertAdjacentHTML("beforeend", reserveGroup);

        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Player\'s reserve')+"</span></div>";
        this.addCustomTooltip( `icon_reserve_${player.id}`, html);


        // green thumbs

        const thumbGroup = `
            <div class="icon-group">
                <div class="icon" id="icon_thumb_${player.id}"></div>
                <span id="thumb_counter_${player.id}" class="icon-text"></span>
            </div>
        `;
        aiBoard.insertAdjacentHTML("beforeend", thumbGroup);

        // random thumb color
        const thumbElement = document.getElementById(`icon_thumb_${player.id}`);
        const color = Math.floor(Math.random() * 5); // Génère un nombre entre 0 et 4
        thumbElement.style.backgroundPosition = `-${color}00% -100%`;

        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Thumbs collected')+"</span></div>";
        this.addCustomTooltip( `icon_thumb_${player.id}`, html);

        if( player.id ==this.player_id) {
                           
            aiBoard.insertAdjacentHTML('beforeend', `
                <div id="help-mode-switch">
                    <input type="checkbox" class="checkbox" id="help-mode-chk" />
                    <label class="label" for="help-mode-chk">
                        <div class="ball"></div>
                    </label>
                    <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <g class="fa-group">
                            <path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path>
                            <path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path>
                        </g>
                    </svg>
                </div>
            `);
            const helpModeSwitchElement = document.getElementById('help-mode-switch');
            helpModeSwitchElement.style.display = 'inline-block';
            const helpModeCheckbox = document.getElementById('help-mode-chk');
            helpModeCheckbox.addEventListener('change', () => {
                this.toggleHelpMode(helpModeCheckbox.checked);
            });
            this.addTooltip("help-mode-switch", "", _("Toggle Tooltips on Mobile mode."));

        }

    });
},


/*  ____                      _  */
/* | __ )  ___   __ _ _ __ __| | */
/* |  _ \ / _ \ / _` | '__/ _` | */
/* | |_) | (_) | (_| | | | (_| | */
/* |____/ \___/ \__,_|_|  \__,_| */

setupBoard: function () {
    console.log('Setting up the board');

    // on ajoute le marché et chaque maison
    // on remplit avec les pièces, les plantes, les tuiles et les pots

    const gameBoard = `
        <div id="resized_id">
            <div id="board_id"></div>
        </div>   
    `
    
    const gamePlayArea = document.getElementById("game_play_area");
    gamePlayArea.insertAdjacentHTML("beforeend", gameBoard);



    this.addScorepad();
    this.addMarket();

    if( this.gamedatas.end_game == "1") {
        this.showFinalScores(this.gamedatas.scoring);
    }

    this.addDecksToMarket();
    this.addPotsToMarket();



    Object.values(this.players_ordered).forEach((player) => {
        this.addHouse(player);
    });


    Object.values(this.gamedatas.rooms).forEach((room) => {
        if(room.location == 'market') {
            this.addCardToMarket(room, 'room')
        }
        else {
           this.addCardToHouse(room, 'room');
        }
    });


    Object.values(this.gamedatas.plants).forEach((plant) => {
        if(plant.location == 'market') {
            this.addCardToMarket(plant, 'plant')
        }
        else {
            if( plant.location_arg < 99) {
                // first round card
                this.addCardToHouse(plant, 'plant');
            }
        }
    });


    Object.values(this.gamedatas.tiles).forEach((tile) => {
        if(tile.location == 'market') {
            this.addTileToMarket(tile)
        }
        else {
            this.addTileToHouse(tile);
            
        }
    });


    Object.values(this.gamedatas.pots).forEach(pot => {
        if (['deck', 'discard', 'market'].includes(pot.location)) 
            return;

        this.addPotToHouse(pot);
    });
},


addScorepad: function() {

    const rowCount = 12; // 12 lignes

    // Créer le tableau de scores
    const scorepad = document.createElement('div');
    scorepad.id = 'final_scorepad_id';
    //scorepad.classList.add('scorepad');
    scorepad.classList.add('scorepad', 'hidden');

    for (let row = 1; row <= rowCount; row++) {
        // Ajouter une colonne à gauche pour chaque ligne
        const leftColumn = document.createElement('div');
        leftColumn.classList.add('pad_space', 'left_column'); // Ajout d'une classe spécifique si besoin
        leftColumn.id = `score_line_${row}`;

        // Calculer la position en pourcentage pour chaque case de la colonne de gauche
        const left = '5%'; // Ajuster selon l'affichage souhaité
        const top = 2 + (row - 1) * 8 + '%'; // Même hauteur que les autres

        // Appliquer les styles de position et de taille
        leftColumn.style.position = 'absolute';
        leftColumn.style.left = left;
        leftColumn.style.top = top;

        // Ajouter la cellule de la colonne de gauche dans le tableau de scores
        scorepad.appendChild(leftColumn);

        // Ajouter les cellules des joueurs
        for (let playerId in this.players) {
            const cell = document.createElement('div');
            cell.classList.add('pad_space');
            cell.id = `score_line_${row}_player_${playerId}`;

            // Calculer la position en pourcentage pour chaque case
            const left = 20 + (Object.keys(this.players).indexOf(playerId) * 15) + '%'; // 15% pour chaque colonne (ajustable)

            // Appliquer les styles de position et de taille
            cell.style.position = 'absolute';
            cell.style.left = left;
            cell.style.top = top;

            // Ajouter la cellule dans le tableau de scores
            scorepad.appendChild(cell);
        }

    }

    // Ajouter le tableau de scores dans le DOM
    document.getElementById("board_id").appendChild(scorepad);


    // Ajout des avatars
    for (const playerId in this.players) {
        const avatarImage = document.getElementById('avatar_'+playerId);
        const avatarSrc = avatarImage.src;

        const newImage = document.createElement('img');
        newImage.src = avatarSrc;
        newImage.id = 'scorepad_avatar_'+playerId; 
        newImage.classList.add('emblem');
        const container = document.getElementById('score_line_1_player_' + playerId);                         
        container.appendChild(newImage);
        this.addTooltipHtml('scorepad_avatar_'+playerId, this.gamedatas.players[playerId].name, '' );
    }


},


/*  __  __            _        _    */
/* |  \/  | __ _ _ __| | _____| |_  */
/* | |\/| |/ _` | '__| |/ / _ \ __| */
/* | |  | | (_| | |  |   <  __/ |_  */
/* |_|  |_|\__,_|_|  |_|\_\___|\__| */


addMarket: function () {
    const marketHTML = `
        <div id="market_id" class="market-container">
            <div id="market_name" class="market-name">
                <span class="market-name-text">${_("Market")}</span>
            </div>
            <div id="market_inner_container" class="market-inner-container">
                <div id="market_advanced" class="market-advanced"></div>
                <div id="market_grid" class="market-grid">
                    ${this.generateMarketGrid()} <!-- Insère dynamiquement la grille -->
                </div>
            </div>
        </div>
    `;

    // Insérer le HTML du Market dans le board
    document.getElementById("board_id").insertAdjacentHTML("beforeend", marketHTML);

    if( this.gamedatas.game_mode >= ADVANCED ) {
        this.addGoalCards();
    }
    else {
        dojo.addClass('market_advanced',  'hidden');
    }
},


generateMarketGrid: function() {
    const rows = 4; // 4 lignes
    const cols = 5; // 5 colonnes

    let gridCellsHTML = '';

    // Boucle sur chaque ligne et colonne pour générer les cellules
    for (let r = 1; r <= rows; r++) { // Les lignes commencent à 1
        for (let c = 1; c <= cols; c++) { // Les colonnes commencent à 1
            const cellId = `market_cell_${r}${c}`;
            const cellClass = (r === 1 || r === 3) ? 'tile-pot' : 'card'; // Ternaire pour la classe
            const cellHeight = (r === 1 || r === 3) ? 'var(--minigrid-height)' : 'var(--card-height)'; // Ternaire pour la hauteur

            gridCellsHTML += `
                <div id="${cellId}" class="market-cell ${cellClass}" style="height: ${cellHeight};">
                    <!-- Vous pouvez ajouter des éléments ou du texte ici si nécessaire -->
                </div>
            `;
        }
    }


    return gridCellsHTML;
},

addDecksToMarket: function () {
     // Case 2,1 : plant_market_deck
    const plantMarketDeck = document.getElementById("market_cell_21");
    plantMarketDeck.innerHTML = `
        <div id="plant_market_deck" class="room plant-deck">
            <span id="nb_plants" class="deck-text plant-text"></span>
        </div>
    `;

    // Case 3,1 : bag_market
    const bagMarket = document.getElementById("market_cell_31");
    bagMarket.innerHTML = `
        <div id="bag_market" class="big-icon bag-icon"></div>
    `;

    // Case 4,1 : room_market_deck
    const roomMarketDeck = document.getElementById("market_cell_41");
    roomMarketDeck.innerHTML = `
        <div id="room_market_deck" class="room room-deck">
            <span id="nb_rooms" class="deck-text room-text"></span>
        </div>
    `;

},

addPotsToMarket: function() {
    const playerCount = Object.keys(this.players).length;

    // Si mode multijoueur
    if (playerCount > 1) {
        for (let i = 0; i < 4; i++) {
            const col = i + 2;
            const type = 3 - i;
            const nb_pot = this.gamedatas.nb_pots_in_market[type] || 0;

            const potContainer = document.getElementById( `market_cell_1${col}`);

            potContainer.innerHTML = `
                <div id="pot_market_${type}" class="pot" style="background-position: -${i}00% 0%;">
                    <span id="nb_pot_market_${type}" class="white-shadow-text">${nb_pot}</span>
                </div>
            `;
        }
    }
    // Mode solo
    else {

        const soloPotContainer = document.getElementById("market_cell_11");
        soloPotContainer.innerHTML = `
            <div id="pot_container_id" class="pot-container">
                ${[3, 2, 2, 1, 1, 0].map((type, index) => {
                      const id = index % 2 === 0 ? `pot_discard_${type}` : `pot_deck_${type}`;
                      const nb_id = index % 2 === 0 ? `nb_pot_discard_${type}` : `nb_pot_deck_${type}`;
                      const type_css = 3 - type;
                    return `
                        <div id="${id}" class="minipot pot-type-${type}" style="background-position: -${type_css * 100}% 0%;">
                            <span id="${nb_id}" class="mini-white-shadow-text">0</span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        this.gamedatas.pots_in_market.forEach(pot => {
            const pot_pos = parseInt(pot.location) + 1;
            const pot_css = 3 - pot.type;

            const soloPotContainer = document.getElementById( `market_cell_1${pot_pos}`);

            soloPotContainer.innerHTML = ` 
                <div id="pot_${pot.id}" class="pot" 
                    style="background-position: -${pot_css}00% 0%;">
                </div>
            `;
        });
    }
},


addGoalCards:function() {
     // Création de la carte Plant Goal
    const plant_goal_type = this.gamedatas.plant_goal; // vert clair
   
    let plant_goal_x;
    let plant_goal_y;
    if( plant_goal_type <= 10) {
        plant_goal_x = plant_goal_type - 1;
        plant_goal_y = 0;
    }
    else if( plant_goal_type == 11) {
        plant_goal_x = 0;
        plant_goal_y = 3;       
    }
    else if( plant_goal_type == 12) {
        plant_goal_x = 1;
        plant_goal_y = 3;       
    }
    else if( plant_goal_type == 13) {
        plant_goal_x = 7;
        plant_goal_y = 3;       
    }

    const plant_goal_id = `plant_goal_${plant_goal_type}`;

    const plantGoalHTML = `<div id="${plant_goal_id}" class="goal" style="background-position: -${plant_goal_x}00% -${plant_goal_y}00%;"></div>
    `;

    const goalContainer = document.getElementById("market_advanced");
    goalContainer.insertAdjacentHTML('beforeend', plantGoalHTML)

    this.addCustomTooltip(plant_goal_id, this.getTooltipPlantGoalContent(plant_goal_type, plant_goal_id));

    // Création de la carte Item Goal
    const item_goal_type = this.gamedatas.item_goal; // vert moyen

    let item_goal_x;
    let item_goal_y;
    if( item_goal_type <= 10) {
        item_goal_x = item_goal_type - 1;
        item_goal_y = 1;
    }
    else if( item_goal_type == 11) {
        item_goal_x = 2;
        item_goal_y = 3;       
    }
    else if( item_goal_type == 12) {
        item_goal_x = 3;
        item_goal_y = 3;       
    }
    else if( item_goal_type == 13) {
        item_goal_x = 8;
        item_goal_y = 3;       
    }

    const item_goal_id = `item_goal_${item_goal_type}`;
    const itemGoalHTML = `<div id="${item_goal_id}" class="goal" style="background-position: -${item_goal_x}00% -${item_goal_y}00%;"></div>
    `;

    goalContainer.insertAdjacentHTML('beforeend', itemGoalHTML)

    this.addCustomTooltip(item_goal_id, this.getTooltipItemGoalContent(item_goal_type, item_goal_id));



    // Création de la carte Plant Goal
    const room_goal_type = this.gamedatas.room_goal; // vert foncé

   let room_goal_x;
    let room_goal_y;
    if( room_goal_type <= 10) {
        room_goal_x = room_goal_type - 1;
        room_goal_y = 2;
    }
    else if( room_goal_type == 11) {
        room_goal_x = 4;
        room_goal_y = 3;       
    }
    else if( room_goal_type == 12) {
        room_goal_x = 5;
        room_goal_y = 3;       
    }
    else if( room_goal_type == 13) {
        room_goal_x = 9;
        room_goal_y = 3;       
    }

    const room_goal_id = `room_goal_${room_goal_type}`;
    const roomGoalHTML = `<div id="${room_goal_id}" class="goal" style="background-position: -${room_goal_x}00% -${room_goal_y}00%;"></div>
    `;

    goalContainer.insertAdjacentHTML('beforeend', roomGoalHTML)
 
    this.addCustomTooltip(room_goal_id, this.getTooltipRoomGoalContent(room_goal_type, room_goal_id));
},




addCardToMarket: function(card, genre) {

    const type = parseInt(card.type) - 1;
    const coord = parseInt(card.location_arg);

    const card_x = type % 12;
    const card_y = Math.floor(type / 12);

    const col = coord + 1;
    const row = genre === 'plant' ? 2 : 4;

    const card_css = `${genre}_${card.type}`;

    const card_cell = document.getElementById(`market_cell_${row}${col}`);
    // Création du HTML de la carte
    card_cell.innerHTML = `
        <div id="${card_css}" class="${genre}" style="background-position: -${card_x}00% -${card_y}00%;">
        </div>
    `;

    const cardElement = document.getElementById(`${card_css}`);

    const nb_thumbs = parseInt(card.thumb);
    if( nb_thumbs > 0) {

        const marketThumbsHTML = `
            <div id="thumbs_${card_css}" class="icon market-thumb">
                <span id="nb_thumbs_${card_css}" class="market-thumb-text"></span>
            </div>
        `;

        cardElement.insertAdjacentHTML('beforeend', marketThumbsHTML);

        const thumbElement = document.getElementById(`thumbs_${card_css}`);
        thumbElement.style.top = genre == 'plant' ? `63%` : `12%`;

        this.thumb_counter[`${card_css}`] = new ebg.counter();
        this.thumb_counter[`${card_css}`].create(`nb_thumbs_${card_css}`);
        this.thumb_counter[`${card_css}`].toValue(nb_thumbs);
    }


    if( genre == 'plant') {
        this.addCustomTooltip(cardElement.id, this.getTooltipPlantContent(card.type, card.id));
    }
    else {
        this.addCustomTooltip(cardElement.id, this.getTooltipRoomContent(card.type, card.id));
    }
},



addTileToMarket: function(tile) {

    const type = parseInt(tile.type);
    const coord = parseInt(tile.location_arg);

    const col = coord + 1;
    const row = 3;

    const tile_x = (type % 10) - 1; // 0 to 8
    const tile_y = Math.floor(type / 10) - 1; // 0 to 5

    const backgroundPosition = tile_y === 5 
        ? `-900% -${tile_x}00%` 
        : `-${tile_x}00% -${tile_y}00%`;


    const tile_cell = document.getElementById(`market_cell_${row}${col}`);
    // Création du HTML de la carte
    tile_cell.innerHTML = `
        <div id="tile_${tile.id}" class="tile" style="background-position: ${backgroundPosition};">
        </div>
    `;

    this.addCustomTooltip(`tile_${tile.id}`, this.getTooltipTileContent(tile.type, tile.id));
},



/*  _   _                       */
/* | | | | ___  _   _ ___  ___  */
/* | |_| |/ _ \| | | / __|/ _ \ */
/* |  _  | (_) | |_| \__ \  __/ */
/* |_| |_|\___/ \__,_|___/\___| */



addHouse: function (player) {
    const board = document.getElementById("board_id");
    const playerColor = player.color.toLowerCase();

    // Applique la couleur du joueur en fond avec plus de clarté
    // Convertir la couleur hex en RGB
    const r = parseInt(playerColor.substr(0, 2), 16);
    const g = parseInt(playerColor.substr(2, 2), 16);
    const b = parseInt(playerColor.substr(4, 2), 16);

    // Crée une version éclaircie de la couleur avec un léger ajout de blanc
    let bgColor;

    if (playerColor === "ffffff") {
        // Si blanc, mettre un fond gris clair
        bgColor = "rgba(230, 230, 230, 0.8)";
    } else {
        // Sinon, éclaircir simplement en ajoutant +100 aux valeurs RGB (max 255)
        const lighterR = Math.min(r + 200, 255);
        const lighterG = Math.min(g + 200, 255);
        const lighterB = Math.min(b + 200, 255);
        bgColor = `rgba(${lighterR}, ${lighterG}, ${lighterB}, 0.8)`;
    }


    const houseHTML = `
        <div id="house_${player.id}" class="house-container" 
             style="border-color: #${playerColor}; background-color: ${bgColor};">
            
            <div id="house_name_${player.id}" class="house-name">
                <span class="player-name" style="color: #${player.color}; 
                    ${player.color.toLowerCase() === "ffffff" ? "text-shadow: 0 0 1px black, 0 0 2px black, 0 0 3px black;" : ""}">
                    ${player.name}
                </span>
            </div>

            <div id="house_grid_container_${player.id}" class="house-grid-container">
                <div id="house_grid_${player.id}" class="house-grid"></div>
            </div>
        </div>
    `;

    board.insertAdjacentHTML("beforeend", houseHTML);
},


addCardToHouse: function(card, genre) {

    const img_type = parseInt(card.type) - 1;
    const player_id = parseInt(card.location);
    const coord = parseInt(card.location_arg);

    const row = Math.floor(coord / 10) + 1;
    const col = (coord % 10) + 1;

    // Récupération de la grille du joueur
    const houseGrid = document.getElementById(`house_grid_${player_id}`);

    // Créer le conteneur `grid-slot` avec un template string
    const slotHTML = `<div id="slot_${coord}_${player_id}" class="grid-slot" style="grid-column-start: ${col}; grid-row-start: ${row};"></div>`;
    
    houseGrid.insertAdjacentHTML("beforeend", slotHTML);


    slotContainer = document.getElementById(`slot_${coord}_${player_id}`);
    

    // Calcul de la position de l'image
    const card_x = img_type % 12;
    const card_y = Math.floor(img_type / 12);

    // Création de la carte sous forme de template string et ajout de Verdancy si présent
    const cardHTML = `
        <div id="${genre}_${card.type}" class="${genre}" data-coord="${coord}"
            style="background-position: -${card_x}00% -${card_y}00%;">
            ${parseInt(card.type_arg) > 0 ? `
                <div id="verdancy_plant_${card.type}" class="icon house-verdancy">
                    <span id="nb_verdancy_plant_${card.type}" class="white-shadow-text"></span>
                </div>` : ""}
        </div>
    `;

    // Ajout de la carte dans le slot
    slotContainer.insertAdjacentHTML("beforeend", cardHTML);


    // Si la carte a du verdancy, initialiser le compteur
    if (parseInt(card.type_arg) > 0) {
        this.verdancy_counter[card.type] = new ebg.counter();
        this.verdancy_counter[card.type].create(`nb_verdancy_plant_${card.type}`);
        this.verdancy_counter[card.type].toValue(parseInt(card.type_arg));
    }


    const cardElement = document.getElementById(`${genre}_${card.type}`);
    if( genre == 'plant') {
        this.addCustomTooltip(cardElement.id, this.getTooltipPlantContent(card.type, card.id));
    }
    else {
        this.addCustomTooltip(cardElement.id, this.getTooltipRoomContent(card.type, card.id));
    }
},



addTileToHouse: function(tile) {

    const type = parseInt(tile.type);
    const player_id = parseInt(tile.location);
    const room_type = parseInt(tile.location_arg);

    const tile_x = (type % 10) - 1; // 0 to 8
    const tile_y = Math.floor(type / 10) - 1; // 0 to 5

    const backgroundPosition = tile_y === 5 
        ? `-900% -${tile_x}00%` 
        : `-${tile_x}00% -${tile_y}00%`;

    // Création de la tuile avec un template string
    const tileHTML = `
        <div id="tile_${tile.id}" class="tile ${room_type != 99 ? 'tile-house' : ''}"
            style="background-position: ${backgroundPosition};">
        </div>
    `;


    // Sélection du bon conteneur pour l'ajout : 99 pour la réserve dans le player's panel
    const container = room_type === 99 
        ? document.getElementById(`icon_reserve_${player_id}`)
        : document.getElementById(`room_${room_type}`);


    container.insertAdjacentHTML("beforeend", tileHTML);


    //if(room_type === 99) {
        this.addCustomTooltip(`tile_${tile.id}`, this.getTooltipTileContent(tile.type, tile.id));
    //}


},

addPotToHouse: function(pot) {

    const cardElement = document.getElementById(`plant_${pot.location_arg}`);
    const pot_sprite = 3- pot.type;

    const housePotHTML = `
        <div id="pot_${pot.id}" class="pot house-pot" style="background-position: -${pot_sprite}00% 0%; ">
        </div>
    `;

    cardElement.insertAdjacentHTML('beforeend', housePotHTML);
},



addPossibleCardPositions: function (grid_coord_pid, genre_type) {
    // add posssible card positions and add them to this.possibles for connections
    const parts = grid_coord_pid.split("_");
    const coord = Number(parts[1]);
    const player_id = Number(parts[2]);

    const [genre, type_str] = genre_type.split("_"); // Sépare en deux parties
    const type = parseInt(type_str, 10) - 1; // Convertit en entier

    let row = Math.floor(coord / 10) + 1;
    let col = coord % 10 + 1;

    const card_x = type % 12;
    const card_y = Math.floor(type / 12);

    // Template HTML pour le slot et la carte
    const slotTemplate = `
        <div id="slot_${coord}_${player_id}" class="grid-slot" style="grid-column-start: ${col}; grid-row-start: ${row};">
            <div id="${grid_coord_pid}" class="${genre} selectable possible_in_house" 
                style="background-position: -${card_x}00% -${card_y}00%;">
            </div>
        </div>
    `;

    const gridContainer = document.getElementById(`house_grid_${player_id}`);
    gridContainer.insertAdjacentHTML('beforeend', slotTemplate);

    if( genre == 'plant') {
        this.addCustomTooltip(grid_coord_pid, this.getTooltipPlantContent(type+1, grid_coord_pid));
    }
    else {
        this.addCustomTooltip(grid_coord_pid, this.getTooltipRoomContent(type+1, grid_coord_pid));
    }

    // Ajout à la liste des éléments possibles
    this.possibles.push(grid_coord_pid);
},




resetCard: async function(card) {
     
    // existant
    const target = document.getElementById(`${card.genre}_${card.type}`);

    // autre face
    const newNode = document.createElement('div');
    newNode.classList.add('room');
        // Appliquer la position de l'image selon le genre de la carte
    if (card.genre === 'plant') {
        newNode.style.backgroundPosition = '-1000% -500%';
    } else {
        newNode.style.backgroundPosition = '-1100% -500%';
    }


    await this.flipAndReplace(target, newNode);
    
    const targetElement = document.getElementById(`${card.genre}_market_deck`);
    // Lancer l'animation de déplacement vers le deck
    await this.slide(newNode, targetElement, { destroy: true });
    //slotContainer.remove();

},




flipAndDrawCard: async function (card) {
    // Créer l'élément target pour la carte
    const target = document.createElement('div');
    target.classList.add(`${card.genre}-deck`, 'room');

    // Ajouter la carte au deck correspondant
    const gridContainer = document.getElementById(`${card.genre}_market_deck`);
    gridContainer.appendChild(target);

    // Calculer la position de l'image de la carte
    const img_type = parseInt(card.type) - 1;
    const card_x = img_type % 12;
    const card_y = Math.floor(img_type / 12);

    const newNode = document.createElement('div');
    newNode.classList.add(card.genre);
    newNode.style.backgroundPosition = `-${card_x * 100}% -${card_y * 100}%`;
    newNode.id = `${card.genre}_${card.type}`;

    // **Flip de la carte**
    await this.flipAndReplace(target, newNode);

    // **Coordonnée et élément cible pour le mouvement (draw)**
    const coord = parseInt(card.location_arg);
    const cardElement = document.getElementById(`${card.genre}_${card.type}`);
    const row = card.genre === 'plant' ? 2 : 4;
    const col = coord + 1;
    const targetElement = document.getElementById(`market_cell_${row}${col}`);

    // **Lancer le slide (draw)**
    await this.slide(cardElement, targetElement);

    // Ajouter un tooltip spécifique en fonction du genre de la carte
    if (card.genre === 'plant') {
        this.addCustomTooltip(cardElement.id, this.getTooltipPlantContent(card.type, card.id));
    } else {
        this.addCustomTooltip(cardElement.id, this.getTooltipRoomContent(card.type, card.id));
    }
},


drawCard: async function(card) { // checked
    const coord = parseInt(card.location_arg);


    // **Face arrière : image de la carte**
    const img_type = parseInt(card.type) - 1;
    const card_x = img_type % 12;
    const card_y = Math.floor(img_type / 12);

    const newNode = document.createElement('div');
    newNode.classList.add(card.genre);
    newNode.style.backgroundPosition = `-${card_x * 100}% -${card_y * 100}%`;
    newNode.id = `${card.genre}_${card.type}`;
    const gridContainer = document.getElementById(`${card.genre}_market_deck`);
    gridContainer.appendChild(newNode);



    const cardElement = document.getElementById(`${card.genre}_${card.type}`);

    
    const row = card.genre == 'plant' ? 2 : 4;
    const col = coord + 1;
    const targetElement = document.getElementById(`market_cell_${row}${col}`);

    // **Lancer le slide**
    await this.slide(cardElement, targetElement);

    if( card.genre == 'plant') {
        this.addCustomTooltip(cardElement.id, this.getTooltipPlantContent(card.type, card.id));
    }
    else {
        this.addCustomTooltip(cardElement.id, this.getTooltipRoomContent(card.type, card.id));
    }
},


drawTile: async function(tile) { // checked

    const type = parseInt(tile.type);
    const coord = parseInt(tile.location_arg);

    const tile_x = type % 10 - 1; // 0 to 8
    const tile_y = Math.floor(type / 10) - 1; // 0 to 5
    // **Création de la nouvelle Tile**
    const tileElement = document.createElement('div');
    tileElement.id = `tile_${tile.id}`;

    tileElement.classList.add('tile');


    const backgroundPosition = tile_y === 5 ? `-900% -${tile_x}00%` : `-${tile_x}00% -${tile_y}00%`;

    tileElement.style.backgroundPosition = backgroundPosition;

    const gridContainer = document.getElementById(`bag_market`);
    gridContainer.appendChild(tileElement);


    const col = coord + 1;
    const targetElement = document.getElementById(`market_cell_3${col}`);


    await this.slide(tileElement, targetElement);

    await this.addCustomTooltip(tileElement.id, this.getTooltipTileContent(tile.type, tileElement.id));
},


addThumbOnCard: function(card, value = 1) {

    const genre = card.genre;

    if( card.thumb > 0) {
        this.thumb_counter[`${genre}_${card.type}`].incValue(value);
    }
    else {

        const marketThumbsHTML = `
            <div id="thumbs_${genre}_${card.type}" class="icon market-thumb">
                <span id="nb_thumbs_${genre}_${card.type}" class="market-thumb-text"></span>
            </div>
        `;
        const cardElement = document.getElementById(`${genre}_${card.type}`);
        cardElement.insertAdjacentHTML('beforeend', marketThumbsHTML);

        const thumbElement = document.getElementById(`thumbs_${genre}_${card.type}`);
        thumbElement.style.top = genre == 'plant' ? `63%` : `12%`;

        this.thumb_counter[`${genre}_${card.type}`] = new ebg.counter();
        this.thumb_counter[`${genre}_${card.type}`].create(`nb_thumbs_${genre}_${card.type}`);
        this.thumb_counter[`${genre}_${card.type}`].toValue(value);

    }
},


animateAndRemoveToken: function(token_css) {

    return new Promise((resolve) => {
        const tokenElement = document.getElementById(token_css);
        if (!tokenElement) {
            resolve(); // Si l'élément n'existe pas, on termine la promesse
            return;
        }

        // Ajouter l'animation CSS
        tokenElement.classList.add("sprite-disappear");

        tokenElement.addEventListener("animationend", () => {
            tokenElement.remove();
            resolve(); // La promesse est terminée
        }, { once: true }); // `once: true` pour éviter plusieurs déclenchements
    });
},

animateAndRemoveVerdancy: function(plant_type) { //checked

    return new Promise((resolve) => {
        const verdancyElement = document.getElementById(`verdancy_plant_${plant_type}`);
        if (!verdancyElement) {
            resolve(); // Si l'élément n'existe pas, on termine la promesse
            return;
        }

        // Ajouter l'animation CSS
        verdancyElement.classList.add("sprite-disappear");

        verdancyElement.addEventListener("animationend", () => {
            verdancyElement.remove();
            resolve(); // La promesse est terminée
        }, { once: true }); // `once: true` pour éviter plusieurs déclenchements
    });
},

animatePotAppearance: function(plant_type, pot_value) { //checked
    return new Promise((resolve) => {
        const cardElement = document.getElementById(`plant_${plant_type}`);
        const pot_sprite = 3 - pot_value;

        const housePotHTML = `
            <div id="pot_house_plant_${plant_type}" class="pot house-pot sprite-appear" style="background-position: -${pot_sprite}00% 0%;">
            </div>
        `;

        

        cardElement.insertAdjacentHTML('beforeend', housePotHTML);

        const potElement = document.getElementById(`pot_house_plant_${plant_type}`);

        // Attendre la fin de l'animation
        potElement.addEventListener("animationend", () => {
            potElement.classList.remove("sprite-appear"); // Retirer la classe pour éviter de rejouer l'animation
            
            // adjust market counter
            this.pot_counter[pot_sprite].incValue(-1);
            resolve();
        }, { once: true });
    });
},

animatePotAppearanceSolo: function(plant_type, pot_value, pot_origin) { //checked
    return new Promise((resolve) => {
        const cardElement = document.getElementById(`plant_${plant_type}`);
        const pot_sprite = 3 - pot_value;

        const housePotHTML = `
            <div id="pot_house_plant_${plant_type}" class="pot house-pot sprite-appear" style="background-position: -${pot_sprite}00% 0%;">
            </div>
        `;

        cardElement.insertAdjacentHTML('beforeend', housePotHTML);

        const oldPotElement = document.getElementById(pot_origin).firstElementChild;
        if( pot_origin == 'market_cell_15')  {
            oldPotElement.classList.add("sprite-disappear");
        }


        const potElement = document.getElementById(`pot_house_plant_${plant_type}`);

        // Attendre la fin de l'animation
        potElement.addEventListener("animationend", () => {
            potElement.classList.remove("sprite-appear"); // Retirer la classe pour éviter de rejouer l'animation
            
            // pot in market place 4 is removed or lowest discard counter is updated
            if( pot_origin == 'market_cell_15')  {
                oldPotElement.remove();
            }
            else {
                if( pot_origin == 'pot_deck_0') { // round13
                    this.pot_deck_counter[0].incValue(-1);
                }
                else {
                    const pot_sprite = pot_origin.slice(-1);
                    this.pot_discard_counter[pot_sprite].incValue(-1);
                }
            }
            resolve();
        }, { once: true });
    });
},


discardTile: function(tile) {
    return new Promise((resolve) => {
        const tileElement = document.getElementById(`tile_${tile.id}`);
        if (!tileElement) {
            resolve(); // Si l'élément n'existe pas, on évite un plantage
            return;
        }

        tileElement.classList.remove("selectable", "selected");
        tileElement.classList.add("sprite-disappear");

        if (tile.location_arg === 99) {
            const parentElement = tileElement.parentElement;
            parentElement.classList.add("tooltipable");
        }

        // Attendre la fin de l'animation avant de supprimer l'élément
        tileElement.addEventListener("animationend", () => {
            tileElement.remove();
            resolve(); // La promesse est résolue après la suppression
        }, { once: true }); // `once: true` pour éviter plusieurs déclenchements
    });
},




moveBackThumbs: async function() {
    const processThumbs = async (prefix, marketCellId) => {
        // Récupérer la cellule de marché
        const marketCell = document.getElementById(marketCellId);
        
        // La carte à l'intérieur de la cellule, par exemple, 'plant_16' ou 'room_32'
        const card = marketCell.firstElementChild;
        if( card) {

            const thumbId = `thumbs_${card.id}`;
    
            // Vérifier si le compteur existe pour cette carte
            if (this.thumb_counter[card.id]) {
                const nbThumbs = this.thumb_counter[card.id].getValue();

                // Si des pouces sont présents, les déplacer
                if (nbThumbs > 0) {
                    // Retirer les pouces de la carte actuelle
                    await this.animateAndRemoveToken(thumbId);

                    // Chercher la première cellule disponible pour y déplacer les pouces
                    for (let i = 4; i >= 2; i--) {
                        const targetCell = document.getElementById(`${prefix === "plant" ? "market_cell_2" : "market_cell_4"}${i}`);

                        if (targetCell) {
                            const targetCard = targetCell.firstElementChild;

                            if (targetCard) {
                                // Vérifier si cette carte cible a déjà des pouces
                                if (this.thumb_counter[targetCard.id]) {
                                    this.thumb_counter[targetCard.id].incValue(nbThumbs);

                                } else {
                                    // Ajouter les pouces à la carte cible s'il n'y en a pas encore
                                    const cardIdSuffix = targetCard.id.split('_')[1]; // Ex: '21' pour 'plant_21'
                                    await this.addThumbOnCard({ type: `${cardIdSuffix}`, genre: prefix }, nbThumbs);
                                }

                                // Quitter la boucle après avoir déplacé les pouces
                                break;
                            }
                        }
                    }

                    // Une fois déplacés, supprimer le compteur de pouces de la carte d'origine
                    delete this.thumb_counter[card.id];
                }
            }
        }
    };

    // Traiter les pouces pour les cartes plant et room
    await Promise.all([
        processThumbs("plant", "market_cell_25"),
        processThumbs("room", "market_cell_45")
    ]);
},

removeThumbOnCard: async function (card) {

    const targetThumbId = `thumbs_${card.genre}_${card.type}`;
    await this.animateAndRemoveToken(targetThumbId);
    delete this.thumb_counter[`${card.genre}_${card.type}`];

},

removeFourthColumn: async function () {
    const elements = ['market_cell_15', 'market_cell_25', 'market_cell_35', 'market_cell_45'];
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    for (let i = 0; i < elements.length; i++) {
        const marketCell = document.getElementById(elements[i]);
        
        // Vérifier si la cellule de marché existe et a un enfant
        if (marketCell.firstElementChild) {
            const child = marketCell.firstElementChild; // Récupérer l'unique enfant de la cellule

            const childId = child.id;

            // Vérifie s'il commence par "pot_"
            if (childId.startsWith('pot_')) {
                const potNum = parseInt(childId.split('_')[1]);

                if (potNum >= 1 && potNum <= 4) {
                    this.pot_discard_counter[3].incValue(1);
                } else if (potNum >= 5 && potNum <= 8) {
                    this.pot_discard_counter[2].incValue(1);
                } else if (potNum >= 9 && potNum <= 12) {
                    this.pot_discard_counter[1].incValue(1);
                }
                // Si potNum > 12 => on ne fait rien
            }

            // Animations et suppression du token (enfant)
            await this.animateAndRemoveToken(childId); 
            await delay(200); // Petit délai pour éviter un retrait trop rapide
        }
    }
},

moveAllRight: async function() {
    const cellsToMove = [
        'market_cell_14', 'market_cell_24', 'market_cell_34', 'market_cell_44',
        'market_cell_13', 'market_cell_23', 'market_cell_33', 'market_cell_43',
        'market_cell_12', 'market_cell_22', 'market_cell_32', 'market_cell_42'
    ];

    const movePromises = [];

    for (let i = 0; i < cellsToMove.length; i++) {
        setTimeout(() => {
            const currentCell = document.getElementById(cellsToMove[i]);
            if (currentCell) {
                const card = currentCell.firstElementChild;
                if (card) {
                    // Calcul de l'ID de la cellule suivante
                    const newCellValue = parseInt(cellsToMove[i].split('_')[2]) + 1;
                    const nextCellId = `market_cell_${newCellValue}`;
                    const nextCell = document.getElementById(nextCellId);

                    if (nextCell) {
                        movePromises.push(this.slide(card, nextCell));
                    }
                }
            }
        }, i * 50); // Décalage de 50ms par élément
    }

    // Attendre que toutes les animations soient terminées
    await Promise.all(movePromises);
},



addNewPot: async function(pot) {
    
    const pot_sprite = 3- pot.type;

    const marketPotHTML = `
        <div id="pot_${pot.id}" class="pot" 
            style="background-position: -${pot_sprite}00% 0%; ">
        </div>
    `;

    const potDeckElement = document.getElementById(`pot_deck_${pot.type}`);
    potDeckElement.insertAdjacentHTML('beforeend', marketPotHTML);

    const potElement = document.getElementById(`pot_${pot.id}`);

    const targetElement = document.getElementById(`market_cell_12`);

    await this.slide(potElement, targetElement);

    this.pot_deck_counter[pot.type].incValue(-1);
},



showFinalScores: function(final_scores) {
    dojo.removeClass( 'final_scorepad_id', 'hidden');
    dojo.addClass( 'market_id', 'hidden'); 

    Object.entries(final_scores).forEach(([playerId, scores]) => {
        const scoreMapping = {
            2: "completed_plants",
            3: "extra_verdancy",
            4: "pot_bonus",
            5: "room_bonus",
            6: "furniture_pets",
            7: "plant_collector_bonus",
            8: "room_collector_bonus",
            9: "plant_goal",
            10: "item_goal",
            11: "room_goal",
            12: "total"
        };

        Object.entries(scoreMapping).forEach(([row, scoreType]) => {
            const cell = document.getElementById(`score_line_${row}_player_${playerId}`);
            if (cell) {
                cell.innerText = scores[scoreType] ?? ""; // Laisser vide si la valeur est undefined
            }
        });
    });

},





setupCounters: function() {

    Object.values(this.players).forEach(player => {
        // Compteur pour les thumbs
        this.thumb_counter[player.id] = new ebg.counter();
        this.thumb_counter[player.id].create('thumb_counter_' + player.id);
        this.thumb_counter[player.id].toValue(player.thumb);
    });

    this.plant_deck = new ebg.counter();
    this.plant_deck.create('nb_plants');
    this.plant_deck.toValue(this.plant_deck_counter);

    this.room_deck = new ebg.counter();
    this.room_deck.create('nb_rooms');
    this.room_deck.toValue(this.room_deck_counter);

    const playerCount = Object.keys(this.players).length;
    if( playerCount > 1 ) {
        for( i=0;i<4;i++) {
            const nb_pot = this.gamedatas.nb_pots_in_market[3-i] || 0;
            this.pot_counter[i] = new ebg.counter();
            this.pot_counter[i].create(`nb_pot_market_${3-i}`);
            this.pot_counter[i].toValue(nb_pot);
        }
    }
    else {
        for( i=0;i<3;i++) {
            const nb_pot = this.gamedatas.nb_pots_in_market[i] || 0;
            this.pot_deck_counter[i] = new ebg.counter();
            this.pot_deck_counter[i].create(`nb_pot_deck_${i}`);
            this.pot_deck_counter[i].toValue(nb_pot);

            const nb_pot_discard = this.gamedatas.nb_pots_in_discard[i+1] || 0;
            this.pot_discard_counter[i+1] = new ebg.counter();
            this.pot_discard_counter[i+1].create(`nb_pot_discard_${i+1}`);
            this.pot_discard_counter[i+1].toValue(nb_pot_discard);
        }
    }
},   




addHelp: function() {
    // Créer l'élément bouton
    const helpButton = document.createElement('div');
    helpButton.id = 'verdant_help1';
    helpButton.className = 'verdant-help-button';
    helpButton.textContent = '?';

    // Ajouter le bouton au body
    document.body.appendChild(helpButton);

    // Ajouter un gestionnaire d'événement pour afficher une aide (modifiable selon besoin)
    helpButton.addEventListener('click', () => {
        this.showHelpModal();
    });

    // Créer l'élément bouton
    const helpButton2 = document.createElement('div');
    helpButton2.id = 'verdant_help2';
    helpButton2.className = 'verdant-help-button';
    helpButton2.textContent = '?';

    // Ajouter le bouton au body
    document.body.appendChild(helpButton2);

    // Ajouter un gestionnaire d'événement pour afficher une aide (modifiable selon besoin)
    helpButton2.addEventListener('click', () => {
        this.showHelpModal2();
    });
    const helpButton3 = document.createElement('div');
    helpButton3.id = 'verdant_help3';
    helpButton3.className = 'verdant-help-button';
    helpButton3.textContent = '?';

    // Ajouter le bouton au body
    document.body.appendChild(helpButton3);

    // Ajouter un gestionnaire d'événement pour afficher une aide (modifiable selon besoin)
    helpButton3.addEventListener('click', () => {
        this.showHelpModal3();
    });      
},


showHelpModal: function() {
    // Vérifie si la modale existe déjà
    if (document.getElementById('helpModal')) return;

    // Création de la modale
    const modal = document.createElement('div');
    modal.id = 'helpModal';
    modal.className = 'modal';


    let html = '<div class="modal-content">';
           html += '<span class="close">&times;</span>';
           html += '<div class="tooltip_content">';

    // Ajout des informations à droite
          html += `<div class="info_container">`;

          html += "<div class='tooltip_bigtitle'>"+_('Turn Summary')+"</div>";

          html += "<div class='tooltip_subtitle'>"+_('Select a Card and a Token')+"</div>";
          html += "<div class='tooltip_desc'>"+_('You must select 1 Item Token and 1 Card (Plant or Room) from the same column.')+"</div>";

          html += "<br><div class='tooltip_subtitle'>"+_('Place the Card into your home')+"</div>";
          html += "<div class='tooltip_desc'>"+_('You will be creating a 5x3 grid of cards in your personal play area, your Home!')+"</div>";
          html += "<div class='tooltip_desc'>"+_('Cards must be placed orthogonally adjacent to other cards. Plant Cards must be placed next to Room Cards, and vice versa, in a checkerboard pattern.')+"</div>";

          html += "<br><div class='tooltip_subtitle'>"+_('Check lighting Conditions and collect Verdancy')+"</div>";
          html += "<div class='tooltip_desc'>"+_('If a match between the Lighting condition on a Room any any of the preferred ones ont the Plant Card is made when placing a Card, then 1 Verdancy is added to the Plant Card.')+"</div>";

          html += "<br><div class='tooltip_subtitle'>"+_('Place/Use Items')+"</div>";
          html += "<div class='tooltip_desc'>"+_('You may place a Pet/Furniture on any Room with a bonus scoring if their type matches.')+"</div>";
           html += "<div class='tooltip_desc'>"+_('You may use a Nurture Item Token to add Verdancy on one or several Plants.')+"</div>";
         
          html += "<br><div class='tooltip_subtitle'>"+_('Completing Plants and adding Pots')+"</div>";
           html += "<div class='tooltip_desc'>"+_('Whenever you complete a plant by adding enough Verdancy, the Plant is potted with the highest value Bonus Pot Token.')+"</div>";

            html += "<br><div class='tooltip_subtitle'>"+_('Store any unused Item Token and check Green Thumb Token limit')+"</div>";
           html += "<div class='tooltip_desc'>"+_('You may store one single Item Token in your Storage location. The others remaining are discarded!')+"</div>";
           html += "<div class='tooltip_desc'>"+_('You may hold a maximum of 5 Green Thumb Tokens from one turn to the next. The excess is discarded back to the supply.')+"</div>";

            html += "<br><div class='tooltip_subtitle'>"+_('Refill the Market')+"</div>";
           html += "<div class='tooltip_desc'>"+_('A Card and a Token are added from the appropriate deck and bag to the Market and your turn is over.')+"</div>";


          html += '</div>'

        html += '</div></div>';

    modal.innerHTML = html;
/*        <div class="modal-content">
            <span class="close">&times;</span>
            let html = '<div class="tooltip_content">'
            <p>Besoin d'aide ? Voici quelques instructions...</p>
        </div>
    `;</div>*/


    document.body.appendChild(modal);

    // Sélection des éléments de la modale
    const closeButton = modal.querySelector('.close');

    // Affichage de la modale
    modal.style.display = 'flex';

    // Fermeture en cliquant sur la croix
    closeButton.addEventListener('click', () => modal.remove());

    // Fermeture en cliquant en dehors de la modale
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.remove();
    });
},

showHelpModal2: function() {
    // Vérifie si la modale existe déjà
    if (document.getElementById('helpModal2')) return;

    // Création de la modale
    const modal = document.createElement('div');
    modal.id = 'helpModal2';
    modal.className = 'modal';

 
    let html = '<div class="modal-content">';
    html += '<span class="close">&times;</span>';
    html += '<div class="tooltip_content">';

    html += `<div class="tile_container">
            <div id="ve_score_toolt" class="big-card" style="background-position: -700% -500%;"></div>
            </div>`;

    // Ajout des informations à droite
    html += `<div class="info_container">`;

    html += "<div class='tooltip_bigtitle'>"+_('Scoring')+"</div>";

    html += "<div class='tooltip_subtitle'>"+_('Completed Plants')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Points indicated below ')+"<span class='inline-icon verdancy' ></span>"+_(' requirement for each completed Plant Card.')+"</div>";

    html += "<br><div class='tooltip_subtitle'>"+_('Extra Verdancy')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Each player scores half of the total number of ')+"<span class='inline-icon verdancy' ></span>"+_(' on incomplete plants.')+"</div>";

    html += "<br><div class='tooltip_subtitle'>"+_('Pots')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Concrete pots score 3 points, Wood pots score 2 points and Ceramic pots score 1 point.')+"</div>";


    html += "<br><div class='tooltip_subtitle'>"+_('Rooms')+"</div>";
    html += "<div class='tooltip_desc'>"+_('1 point scored per adjacent matching plant. Matching Pet/Furniture doubles the points.')+"</div>";
    
    html += "<br><div class='tooltip_subtitle'>"+_('Items')+"</div>";
    html += "<div class='tooltip_desc'>"+_('1, 3, 6, 9, 12, 16, 20, 25 points scored for unique Pet/Furniture tokens in their home.')+"</div>";

    html += "<br><div class='tooltip_subtitle'>"+_('Collector & Decorator')+"</div>";
    html += "<div class='tooltip_desc'>"+_('3 points if home contains all of the 5 different plant types.')+"</div>";
    html += "<div class='tooltip_desc'>"+_('3 points if home contains all of the 5 different room types.')+"</div>";

    html += '</div>'

    html += '</div></div>';

    modal.innerHTML = html;


    document.body.appendChild(modal);

    // Sélection des éléments de la modale
    const closeButton = modal.querySelector('.close');

    // Affichage de la modale
    modal.style.display = 'flex';

    // Fermeture en cliquant sur la croix
    closeButton.addEventListener('click', () => modal.remove());

    // Fermeture en cliquant en dehors de la modale
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.remove();
    });
},

showHelpModal3: function() {
    // Vérifie si la modale existe déjà
    if (document.getElementById('helpModal2')) return;

    // Création de la modale
    const modal = document.createElement('div');
    modal.id = 'helpModal2';
    modal.className = 'modal';

 
    let html = '<div class="modal-content">';
    html += '<span class="close">&times;</span>';
    html += '<div class="tooltip_content">';

    html += `<div class="tile_container">
            <div id="ve_score_toolt" class="big-card" style="background-position: -800% -500%;"></div>
            </div>`;

    // Ajout des informations à droite
    html += `<div class="info_container">`;

    html += "<div class='tooltip_bigtitle'>"+_('Green  Thumb Actions')+"</div>";

    html += "<div class='tooltip_subtitle'>"+_('Spend 2 Thumbs at the start of your round')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Wipe any number of cards without any thumbs.')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Wipe any number of tokens without any thumbs.')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Draft any card and any token.')+"</div>";
    html += "<div class='tooltip_subtitle'>"+_('Spend 2 Thumbs during your round')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Add 1 ')+"<span class='inline-icon verdancy' ></span>"+_(' to any plant.')+"</div>";

    html += "<br><div class='tooltip_bigtitle'>"+_('Nurture Actions')+"</div>";
    html += "<div class='tooltip_subtitle'>"+_('Fertilizer')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Add up to 3 ')+"<span class='inline-icon verdancy' ></span>"+_(' to 1 plant.')+"</div>";

    html += "<br><div class='tooltip_subtitle'>"+_('Hand Trowel')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Add 1 ')+"<span class='inline-icon verdancy' ></span>"+_(' to up to 3 plants.')+"</div>";


    html += "<br><div class='tooltip_subtitle'>"+_('Watering Can')+"</div>";
    html += "<div class='tooltip_desc'>"+_('Add 1 ')+"<span class='inline-icon verdancy' ></span>"+_(' to all plants adjacent to a single room.')+"</div>";
    
    html += '</div>'

    html += '</div></div>';

    modal.innerHTML = html;

    document.body.appendChild(modal);

    // Sélection des éléments de la modale
    const closeButton = modal.querySelector('.close');

    // Affichage de la modale
    modal.style.display = 'flex';

    // Fermeture en cliquant sur la croix
    closeButton.addEventListener('click', () => modal.remove());

    // Fermeture en cliquant en dehors de la modale
    window.addEventListener('click', (event) => {
        if (event.target === modal) modal.remove();
    });
},

/*  _____           _ _   _            */
/* |_   _|__   ___ | | |_(_)_ __  ___  */
/*   | |/ _ \ / _ \| | __| | '_ \/ __| */
/*   | | (_) | (_) | | |_| | |_) \__ \ */
/*   |_|\___/ \___/|_|\__|_| .__/|___/ */
/*                         |_|         */

setupTooltips:function () {

    // Plants deck
    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Plants deck')+"</span></div>";
    this.addCustomTooltip( `plant_market_deck`, html);

    // Rooms Deck
    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Rooms deck')+"</span></div>";
    this.addCustomTooltip( `room_market_deck`, html);

    // Tiles Bag
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Bag of Tiles')+"</span></div>";
    this.addCustomTooltip( `bag_market`, html);

    const playerCount = Object.keys(this.players).length;
    if( playerCount == 1 ) {

        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Concrete pots discarded')+"</span></div>";
        this.addCustomTooltip( `pot_discard_3`, html);
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Wood pots discarded')+"</span></div>";
        this.addCustomTooltip( `pot_discard_2`, html);
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Ceramic pots discarded')+"</span></div>";
        this.addCustomTooltip( `pot_discard_1`, html);

        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Wood pots remaining')+"</span></div>";
        this.addCustomTooltip( `pot_deck_2`, html);
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Ceramic pots remaining')+"</span></div>";
        this.addCustomTooltip( `pot_deck_1`, html);
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Terra Cotta pots remaining')+"</span></div>";
        this.addCustomTooltip( `pot_deck_0`, html);
    }

    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Players')+"</span></div>";
    this.addCustomTooltip( `score_line_1`, html);

    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Completed Plants')+"</span>";
    html += "<span class='tooltip_desc'>"+_('Points indicated below Verdancy requirement for each completed Plant Card.')+"</span></div>";
     this.addCustomTooltip( `score_line_2`, html);    
   
    
  
    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Extra Verdancy on Incomplete Plants')+"</span>";
    html += "<span class='tooltip_desc'>"+_('Each player scores half of the total number of Verdancy on incomplete plants.')+"</span></div>";
    this.addCustomTooltip( `score_line_3`, html);


    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Bonus Pot Tokens')+"</span>";
    html += "<span class='tooltip_desc'>"+_('Concrete pots score 3 points, Wood pots score 2 points and Ceramic pots score 1 point.')+"</span></div>";
    this.addCustomTooltip( `score_line_4`, html); 


    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Room Bonuses')+"</span>";
    html += "<span class='tooltip_desc'>"+_('1 point scored per adjacent matching plant. Matching Pet/Furniture doubles the points.')+"</span></div>";
    this.addCustomTooltip( `score_line_5`, html);


    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Furniture and Pets')+"</span>";
    html += "<span class='tooltip_desc'>"+_('1, 3, 6, 9, 12, 16, 20, 25 points scored for unique Pet/Furniture tokens in their home.')+"</span></div>";
    this.addCustomTooltip( `score_line_6`, html);   


    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Plant Collector Bonus')+"</span>";
    html += "<span class='tooltip_desc'>"+_('3 points if home contains at least 1 of the 5 different plant types.')+"</span></div>";
    this.addCustomTooltip( `score_line_7`, html);


    html = "<div class='tooltip_content'><span class='tooltip_subtitle'>"+_('Decorator Bonus')+"</span>";
    html += "<span class='tooltip_desc'>"+_('3 points if home contains at least 1 of the 5 different room types.')+"</span></div>";
    this.addCustomTooltip( `score_line_8`, html);


    if( this.gamedatas.game_mode >= ADVANCED ) {
        const plant_goal_type = this.gamedatas.plant_goal; // vert clair
        const plant_goal_id = `plant_goal_${plant_goal_type}`;

        this.addCustomTooltip(`score_line_9`, this.getTooltipPlantGoalContent(plant_goal_type, plant_goal_id));
    } 
    else {
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Plant Goal Bonus')+"</span></div>";
        this.addCustomTooltip( `score_line_9`, html);
    }   
    
    if( this.gamedatas.game_mode >= ADVANCED ) {
        const item_goal_type = this.gamedatas.item_goal; // vert moyen
        const item_goal_id = `item_goal_${item_goal_type}`;

        this.addCustomTooltip(`score_line_10`, this.getTooltipItemGoalContent(item_goal_type, item_goal_id));
    }
    else {
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Item Goal Bonus')+"</span></div>";
        this.addCustomTooltip( `score_line_10`, html); 
    }
  


    if( this.gamedatas.game_mode >= ADVANCED ) {
        const room_goal_type = this.gamedatas.room_goal; // vert foncé
        const room_goal_id = `room_goal_${room_goal_type}`;
 
        this.addCustomTooltip(`score_line_11`, this.getTooltipRoomGoalContent(room_goal_type, room_goal_id));

    }
    else {
        html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Room Goal Bonus')+"</span></div>";
        this.addCustomTooltip( `score_line_11`, html);
    }
    


    html = "<div class='tooltip_content'><span class='tooltip_desc'>"+_('Total Scores')+"</span></div>";
    this.addCustomTooltip( `score_line_12`, html);

},

getTooltipTileContent : function(type, id) {

    let html = '<div class="tooltip_content">';

    // Calcul de la position de l'image
    const x = type % 10;
    const y = Math.floor(type / 10);

    const tile_x =  type % 10 - 1 + '00%'; // 0 to 8
    const tile_y = Math.floor(type / 10) - 1 + '00%'; // 0 to 5

    let style;
    let tile_infos;
    if( y == 6) {
        const tool_y = tile_x;
        style = `-900% -${tool_y}`;
        tile_infos = this.gamedatas.nurture_types[x];
    }
    else {
        style = `-${tile_x} -${tile_y}`;
        tile_infos = this.gamedatas.item_types[x];
    }


    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="ve_tile_toolt_${id}" class="tile" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des Troops    
    html += `<span class='tooltip_title'>${_(tile_infos.name)}</span>`;

    if( y == 6) {
        html += `<br><span class='tooltip_desc'>${_(tile_infos.description)}</span>`;
    }
    
    html += '</div></div>'; // Fermeture des div   

    return html;          
},

getTooltipRoomContent : function(type, id) {

    let html = '<div class="tooltip_content">';

    const img_type = parseInt(type) - 1;

    const card_x =  img_type % 12;
    const card_y = Math.floor(img_type / 12);
    const style = `-${card_x}00% -${card_y}00%`;

    const room_infos = this.gamedatas.room_cards[type];



    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="ve_room_toolt_${id}" class="room" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des rooms 
    const plant_type = this.gamedatas.plant_types[room_infos.type].name;
    html += `<span class='tooltip_title'>${_(plant_type)}</span>`;

    
    const lightning_types = ["full-sun", "semi-shade", "shade"];
    const directions = ["north", "east", "south", "west"];

    directions.forEach(direction => {
        const lightning_value = room_infos.lightning[direction];
        const lightning_class = lightning_types[lightning_value - 1];

    html += `
                   
        <div class="lightning-container">
            <span class='tooltip_desc'>${_(direction)}:</span>
            <span class="lightning ${lightning_class}"></span>
        </div>`
        ;
    });
     
    html += '</div></div>'; // Fermeture des div   
    
    return html;          
},

getTooltipPlantContent : function(type, id) {
    let html = '<div class="tooltip_content">';

    const img_type = parseInt(type) - 1;

    const card_x =  img_type % 12;
    const card_y = Math.floor(img_type / 12);
    const style = `-${card_x}00% -${card_y}00%`;

    const plant_infos = this.gamedatas.plant_cards[type];



    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="ve_plant_toolt_${id}" class="plant" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des rooms 

    const plant_name = plant_infos.name;
    html += `<span class='tooltip_title'>${_(plant_name)}</span>`;


    const plant_type = this.gamedatas.plant_types[plant_infos.type].name;
    html += `<br><span class='tooltip_desc'>${_(plant_type)}</span>`;

    const lightning_types = ["full-sun", "semi-shade", "shade"];
    const lightnings = plant_infos.lightning;
    let lightning_html = '';

    lightnings.forEach(lightning => {
        const lightning_class = lightning_types[lightning - 1]; // Associe le type à la classe CSS
        lightning_html += `<span class="lightning ${lightning_class}"></span>`;
    });

    html += `
        <br>
        <div class="lightning-container">
            <span class='tooltip_desc'>${_("Lightings:")}</span>
            ${lightning_html}
        </div>
    `;

    const verdancy = plant_infos.verdancy;
    html += `<br><span class='tooltip_desc'>${_("Verdancy needed: " + verdancy)}</span>`;

    const award = plant_infos.points;
    html += `<br><span class='tooltip_desc'>${_("Plant award: " + award)}</span>`;

    const latin = plant_infos.latin_name;
    html += `<br><br><span class='tooltip_info'>${_(latin)}</span>`;
     
    html += '</div></div>'; // Fermeture des div   
    
    return html;          
},

getTooltipPlantGoalContent : function( plant_goal_type, id) {
    let html = '<div class="tooltip_content">';

    let plant_goal_x;
    let plant_goal_y;
    if( plant_goal_type <= 10) {
        plant_goal_x = plant_goal_type - 1;
        plant_goal_y = 0;
    }
    else if( plant_goal_type == 11) {
        plant_goal_x = 0;
        plant_goal_y = 3;       
    }
    else if( plant_goal_type == 12) {
        plant_goal_x = 1;
        plant_goal_y = 3;       
    }
    else if( plant_goal_type == 13) {
        plant_goal_x = 7;
        plant_goal_y = 3;       
    }

    const style = `-${plant_goal_x}00% -${plant_goal_y}00%`;

    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="toolt_${id}" class="goal" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des rooms 
    const plant_goal_infos = this.gamedatas.plant_goal_cards[plant_goal_type];

    const plant_name = plant_goal_infos.name;
    html += `<span class='tooltip_title'>${_(plant_name)}</span><hr class="dark-green-line">`;

    const award = plant_goal_infos.points;
    html += `<br><span class='tooltip_desc'>${_("Plant goal award: " + award)}</span>`;


    const plant_goal_description = plant_goal_infos.description;
    html += `<br><br><span class='tooltip_desc'>${_(plant_goal_description)}</span>`;
     
    html += '</div></div>'; // Fermeture des div   
    
    return html;          
},

getTooltipItemGoalContent : function( item_goal_type, id) {
    let html = '<div class="tooltip_content">';

    let item_goal_x;
    let item_goal_y;
    if( item_goal_type <= 10) {
        item_goal_x = item_goal_type - 1;
        item_goal_y = 1;
    }
    else if( item_goal_type == 11) {
        item_goal_x = 2;
        item_goal_y = 3;       
    }
    else if( item_goal_type == 12) {
        item_goal_x = 3;
        item_goal_y = 3;       
    }
    else if( item_goal_type == 13) {
        item_goal_x = 8;
        item_goal_y = 3;       
    }

    const style = `-${item_goal_x}00% -${item_goal_y}00%`;

    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="toolt_${id}" class="goal" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des rooms 
    const item_goal_infos = this.gamedatas.item_goal_cards[item_goal_type];

    const item_name = item_goal_infos.name;
    html += `<span class='tooltip_title'>${_(item_name)}</span><hr class="dark-green-line">`;

    const award = item_goal_infos.points;
    html += `<br><span class='tooltip_desc'>${_("Item goal award: " + award)}</span>`;


    const item_goal_description = item_goal_infos.description;
    html += `<br><br><span class='tooltip_desc'>${_(item_goal_description)}</span>`;
     
    html += '</div></div>'; // Fermeture des div   
    
    return html;          
},

getTooltipRoomGoalContent : function( room_goal_type, id) {
    let html = '<div class="tooltip_content">';

    let room_goal_x;
    let room_goal_y;
    if( room_goal_type <= 10) {
        room_goal_x = room_goal_type - 1;
        room_goal_y = 2;
    }
    else if( room_goal_type == 11) {
        room_goal_x = 3;
        room_goal_y = 3;       
    }
    else if( room_goal_type == 12) {
        room_goal_x = 4;
        room_goal_y = 3;       
    }
    else if( room_goal_type == 13) {
        room_goal_x = 9;
        room_goal_y = 3;       
    }

    const style = `-${room_goal_x}00% -${room_goal_y}00%`;

    
    // Ajout de la tile (image) à gauche
    html += `<div class="tile_container">
               <div id="toolt_${id}" class="goal" style="background-position:${style};"></div>
            </div>`;
    
    // Ajout des informations à droite
    html += `<div class="info_container">`;

   
    // Afficher les informations des rooms 
    const room_goal_infos = this.gamedatas.room_goal_cards[room_goal_type];

    const room_name = room_goal_infos.name;
    html += `<span class='tooltip_title'>${_(room_name)}</span><hr class="dark-green-line">`;

    const award = room_goal_infos.points;
    html += `<br><span class='tooltip_desc'>${_("Room goal award: " + award)}</span>`;


    const room_goal_description = room_goal_infos.description;
    html += `<br><br><span class='tooltip_desc'>${_(room_goal_description)}</span>`;
     
    html += '</div></div>'; // Fermeture des div   
    
    return html;          
},

onScreenWidthChange: function () {
    this.updateLayout();
},

updateLayout: function () {
/*    var gameWidth = TABLE_WIDTH;
    var gameHeight = TABLE_HEIGHT;

    var horizontalScale = document.getElementById('game_play_area').clientWidth / gameWidth;
    var verticalScale = (window.innerHeight - 0) / gameHeight;

    var scale = Math.min(1, horizontalScale, verticalScale);

    var resized_div = document.getElementById('resized_id');
    var board = document.getElementById('board_id');

    if (!resized_div || !board) {
        console.warn('updateLayout aborted: missing resized_id or board_id');
        return;
    }
    else {
        console.log('updateLayout');
    }

    var play_area_height = dojo.marginBox(board).h;

    resized_div.style.transform = scale === 1 ? '' : "scale(" + scale + ")";
    dojo.style(resized_div, 'height', (play_area_height * scale) + 'px');*/
},

///////////////////////////////////////////////////////////////////////////////// 
//       _   _       _   _  __ _           _   _                 
//      | \ | |     | | (_)/ _(_)         | | (_)                
//      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
//      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
//      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
//      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
//                                                                 
/////////////////////////////////////////////////////////////////////////////////  



notif_moveCardToHouse: async function(args) {

    // on nettoie la maison
    // on ajoute ou on laisse la carte choisie
    // on récupère les pouces
    // on retire la carte du market

    // args
    // card_before : la carte avant son déplacement
    // card_after  : la carte après son déplacement


    const player_id =  args.card_after.location;
    const house_location = args.card_after.location_arg;

    let row = Math.floor(house_location / 10) + 1;
    let col = house_location % 10 + 1;

    const market_location = `${args.card_before.genre}_${args.card_before.type}`;

    if( args.card_before.thumb > 0 ) {

        this.thumb_counter[player_id].incValue(args.card_before.thumb);
        delete this.thumb_counter[market_location];

        const origin_thumbs_id = `thumbs_${market_location}`;
        const thumbElement = document.getElementById(origin_thumbs_id);
        thumbElement.remove();
    }


    if( player_id == this.player_id ) {


        const possibleElements = document.querySelectorAll('.possible_in_house');
        possibleElements.forEach(element => {
            element.parentNode?.remove();
        });
    }


    if( args.card_before.location_arg != 99) {
        
        // dynamic container is added
        const slotHTML = `
            <div id="slot_${house_location}_${player_id}" class="grid-slot" 
                style="grid-column-start: ${col}; grid-row-start: ${row};">
            </div>
        `;

        const houseGrid = document.getElementById(`house_grid_${player_id}`);
        houseGrid.insertAdjacentHTML("beforeend", slotHTML);

        const slotContainer = document.getElementById(`slot_${house_location}_${player_id}`);
        
        // card is moved
        await this.slide(market_location, slotContainer);
    }
    else {
        this.addCardToHouse(args.card_after, args.card_before.genre);
    }
},






notif_moveTileToHouse: async function(args) {
    
    const tile_id = `tile_${args.tile.id}`;
    const tileElement = document.getElementById(tile_id);

    const destination_id = `room_${args.card.type}`;
    const destinationElement = document.getElementById(destination_id);

    tileElement.classList.remove("selectable", "selected");

    // tile is moved in room and centered
    await this.slide(tileElement, destinationElement);
    tileElement.classList.add('tile-house');
},


notif_discardTile: async function(args) {

    // tile animates and disappears
    await this.discardTile(args.tile);
},


notif_moveTileToReserve: async function(args) {

    // on déplace une tile du Market vers la Réserve du joueur

    // tile animation
    const tile_id = `tile_${args.tile.id}`;
    const tileElement = document.getElementById(tile_id);

    const destination_id = `icon_reserve_${args.player_id}`;
    const destinationElement = document.getElementById(destination_id);

    destinationElement.classList.remove("tooltipable");
    tileElement.classList.remove("selectable", "selected");

    await this.slide(tileElement, destinationElement);
},


notif_addGreenThumbs: async function(args) {
    this.thumb_counter[args.player_id].incValue(args.nb_thumbs);
},

notif_setGreenThumbs: async function(args) {
    this.thumb_counter[args.player_id].setValue(args.nb_thumbs);
},


notif_addVerdancy: async function(args) {

    // s'il y a déjà de la Verdancy, on augmente le Counter.
    // s'il n'y a pas de Verdancy, on ajoute le sprite avec la valeur.

    const plant_type = args.plant_type;
    const cardElement = document.getElementById(`plant_${plant_type}`);

    const verdancy_needed = this.gamedatas.plant_cards[plant_type].verdancy;

    if (document.getElementById(`verdancy_plant_${plant_type}`)) {
        this.verdancy_counter[plant_type].incValue(args.verdancy_added);
    } 
    else {
        const houseVerdancyHTML = `
            <div id="verdancy_plant_${plant_type}" class="icon house-verdancy">
                <span id="nb_verdancy_plant_${plant_type}" class="white-shadow-text"></span>
            </div>
        `;
        cardElement.insertAdjacentHTML('beforeend', houseVerdancyHTML);

        this.verdancy_counter[plant_type] = new ebg.counter();
        this.verdancy_counter[plant_type].create(`nb_verdancy_plant_${plant_type}`);
        this.verdancy_counter[plant_type].toValue(args.verdancy_added);
    }

    // si on a atteint le nombre pour avoir un pot,
    // on enlève le sprite Verdancy et on remplace par un pot.


    // Vérifier si on a atteint le seuil pour enlever le sprite Verdancy
    if (this.verdancy_counter[plant_type].getValue() == verdancy_needed) {
        await this.wait(args.verdancy_added * 300); 
        await this.animateAndRemoveVerdancy(plant_type);
        await this.animatePotAppearance(plant_type, args.pot_value);
    }

    // thumbs action D
    if( args.thumbs_used == true) {
        this.thumb_counter[args.player_id].incValue(-2);
    }
},

notif_addVerdancySolo: async function(args) {
    // s'il y a déjà de la Verdancy, on augmente le Counter.
    // s'il n'y a pas de Verdancy, on ajoute le sprite avec la valeur.

    const plant_type = args.plant_type;

    const cardElement = document.getElementById(`plant_${plant_type}`);

    const verdancy_needed = this.gamedatas.plant_cards[plant_type].verdancy;

    if (document.getElementById(`verdancy_plant_${plant_type}`)) {
        this.verdancy_counter[plant_type].incValue(args.verdancy_added);
    } 
    else {
        const houseVerdancyHTML = `
            <div id="verdancy_plant_${plant_type}" class="icon house-verdancy">
                <span id="nb_verdancy_plant_${plant_type}" class="white-shadow-text"></span>
            </div>
        `;
        cardElement.insertAdjacentHTML('beforeend', houseVerdancyHTML);

        this.verdancy_counter[plant_type] = new ebg.counter();
        this.verdancy_counter[plant_type].create(`nb_verdancy_plant_${plant_type}`);
        this.verdancy_counter[plant_type].toValue(args.verdancy_added);
    }

    // si on a atteint le nombre pour avoir un pot,
    // on enlève le sprite Verdancy et on remplace par un pot.


        // Vérifier si on a atteint le seuil pour enlever le sprite Verdancy
    if (this.verdancy_counter[plant_type].getValue() == verdancy_needed) {
        await this.wait(args.verdancy_added * 300); 
        await this.animateAndRemoveVerdancy(plant_type);
        await this.animatePotAppearanceSolo(plant_type, args.pot_value, args.pot_origin);
    }

    if( args.thumbs_used == true) {
        this.thumb_counter[args.player_id].incValue(-2);
    }
},






notif_refillMarket: async function(args) {
    // Ajouter la vignette sur la carte
    await this.addThumbOnCard(args.card_thumb);

    // Lancer les deux animations simultanément avec un petit délai
    await Promise.all([
        this.flipAndDrawCard(args.card_pick), new Promise(resolve => setTimeout(resolve, 100))
        .then(() => this.drawTile(args.tile_pick)) // délai de 100ms avant drawTile
    ]);
},



notif_refillMarketSolo: async function(args) {
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    // on place un pouce vert en face card_thumb et genre
    await this.addThumbOnCard(args.card_thumb);


    // on déplace les pouces de la 4ème colonne vers la gauche et on supprime si >=3
    await this.moveBackThumbs();

        // Attendre un petit instant pour être sûr que la colonne est bien vide
    await delay(500);

    if (args.thumbs_removed.length > 0) {
        const thumbRemovePromises = args.thumbs_removed.map((card, index) => {
            return new Promise(resolve => {
                setTimeout(async () => {
                    await this.removeThumbOnCard(card);
                    resolve();
                }, index * 200); // 200ms de décalage entre chaque suppression
            });
        });

        await Promise.all(thumbRemovePromises);
    }

    //on supprime les cartes, item et pot sur la colonne 4
    await this.removeFourthColumn();

    // on décale tout d'une case vers la droite
    await this.moveAllRight();



    // Lancer l'animation du pot avec un petit délai avant de commencer les cartes et tuiles
    const potPromise = new Promise(resolve => {
        setTimeout(async () => {
            await this.addNewPot(args.new_pot);
            resolve();
        }, 200); // Légère attente avant l'animation du pot
    });

    // Animer les cartes en parallèle avec un décalage progressif
    const cardPromises = Object.entries(args.cards_pick).map(([index, card]) => {
        return new Promise(resolve => {
            setTimeout(async () => {
                //await this.flipAndDrawCard(card);
                await this.drawCard(card);
                resolve();
            }, index * 300); // Décalage de 300ms entre chaque carte
        });
    });

    // Animer les tuiles en parallèle avec un décalage progressif
    const tilePromises = Object.entries(args.tiles_pick).map(([index, tile]) => {
        return new Promise(resolve => {
            setTimeout(async () => {
                console.log('tile110 ', tile.id);
                await this.drawTile(tile);
                resolve();
            }, index * 300); // Décalage de 300ms entre chaque tuile
        });
    });

    // Attendre que toutes les animations soient terminées
    await Promise.all([potPromise, ...cardPromises, ...tilePromises]);

    
},





notif_resetTiles: async function(args) {
    // Supprimer les anciennes tuiles en parallèle avec un décalage progressif
    const discardPromises = args.old_tiles.map((tile, index) => {
        return new Promise(resolve => {
            setTimeout(() => {
                this.discardTile(tile).then(resolve); // Attendre la fin de l'animation CSS
            }, index * 100); // Décalage de 100ms entre chaque suppression
        });
    });

    // **Attendre que toutes les tuiles aient été supprimées avant d'ajouter les nouvelles**
    await Promise.all(discardPromises);

    // Ajouter un petit délai supplémentaire pour éviter toute collision résiduelle
    await new Promise(resolve => setTimeout(resolve, 200));

    // Ajouter les nouvelles tuiles en parallèle avec un décalage progressif
    const drawPromises = args.new_tiles.map((tile, index) => {
        return new Promise(resolve => {
            setTimeout(() => {
                this.drawTile(tile).then(resolve);
            }, index * 300); // Décalage de 300ms entre chaque ajout
        });
    });

    // Attendre que toutes les nouvelles tuiles aient été ajoutées
    await Promise.all(drawPromises);

    // Mettre à jour le compteur après les animations
    this.thumb_counter[args.player_id].incValue(-2);
},


notif_resetCards: async function(args) {

    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    // Suppression des anciennes cartes en parallèle avec délais
    await Promise.all(
        Object.entries(args.old_cards).map(([index, card]) => 
            delay(index * 200).then(() => this.resetCard(card))
        )
    );


    // Petit délai avant d'ajouter les nouvelles cartes
    await new Promise(resolve => setTimeout(resolve, 200));

    // Ajout des nouvelles cartes en parallèle avec délais
    await Promise.all(
        Object.entries(args.new_cards).map(([index, card]) => 
            delay(index * 600).then(() => {
                //this.flipAndDrawCard(card);
                this.drawCard(card);
                (card.genre === 'plant' ? this.plant_deck : this.room_deck).incValue(-1);
            })
        )
    );

    this.thumb_counter[args.player_id].incValue(-2);
},


notif_useThumbs: async function(args) {
    this.thumb_counter[args.player_id].incValue(-2);
},


notif_showFinalScores: async function(args) {

    this.showFinalScores(args.final_scores); 
},

            //<div id="points_${pot.id}" class="points" style="background-position: -${pot.type}00% 0%; "></div>

notif_score: async function( args ){
    
    this.scoreCtrl[ args.playerid ].toValue( args.score );
},




/*******************************
 ****** UTILS TISAAC *******
 ******************************/


/*******************************
 ****** HELP MODE TISAAC *******
    ******************************/
/**
 * Toggle help mode
 */
toggleHelpMode(b) {
    if (b) 
        this.activateHelpMode();
    else 
        this.desactivateHelpMode();
},

activateHelpMode() {
    this._helpMode = true;
    dojo.addClass('ebd-body', 'help-mode');
    this._displayedTooltip = null;
    document.body.addEventListener('click', this.closeCurrentTooltip.bind(this));
},

desactivateHelpMode() {
    this.closeCurrentTooltip();
    this._helpMode = false;
    dojo.removeClass('ebd-body', 'help-mode');
    document.body.removeEventListener('click', this.closeCurrentTooltip.bind(this));
},

closeCurrentTooltip() {
    if (!this._helpMode) 
        return;
    if (this._displayedTooltip == null) 
        return;
    else {
        this._displayedTooltip.close();
        this._displayedTooltip = null;
    }
},

    /*
    * Custom connect that keep track of all the connections
    *  and wrap clicks to make it work with help mode
    */
connect(node, action, callback) {
    this._connections.push(dojo.connect($(node), action, callback));
},

onClick(node, callback, temporary = true) {
    let safeCallback = (evt) => {
        evt.stopPropagation();
        if (this.isInterfaceLocked()) 
            return false;
        if (this._helpMode) 
            return false;
        callback(evt);
    };

    if (temporary) {
        this.connect($(node), 'click', safeCallback);
        dojo.removeClass(node, 'unselectable');
        dojo.addClass(node, 'selectable');
        this._selectableNodes.push(node);
    } else {
        dojo.connect($(node), 'click', safeCallback);
    }
},

    /**
     * Tooltip to work with help mode
     */


addCustomTooltip(id, html, config = {}) {
    config = Object.assign(
        {
            delay: 400,
            midSize: true,
            forceRecreate: false,
        },
        config,
    );

    let isMobile = window.matchMedia('(pointer: coarse)').matches;
    let longPressTimer = null;

    let getContent = () => {
        let content = typeof html === 'function' ? html() : html;
        if (config.midSize) {
            content = '<div class="midSizeDialog">' + content + '</div>';
        }
        return content;
    };

    if (this.tooltips[id] && !config.forceRecreate) {
        this.tooltips[id].getContent = getContent;
        return;
    }

    let tooltip = new dijit.Tooltip({
        getContent,
        position: this.defaultTooltipPosition,
        showDelay: config.delay,
    });
    this.tooltips[id] = tooltip;
    dojo.addClass(id, 'tooltipable');

    // Empêcher l'affichage au simple clic sur mobile
    dojo.connect($(id), 'click', (evt) => {
        if (isMobile && !this._helpMode) {
            evt.stopPropagation();
            return; // Bloque l'affichage du tooltip sur mobile sauf en mode help
        }

        if (!this._helpMode) {
            tooltip.close();
        } else {
            evt.stopPropagation();

            if (tooltip.state === 'SHOWING') {
                this.closeCurrentTooltip();
            } else {
                this.closeCurrentTooltip();
                tooltip.open($(id));
                this._displayedTooltip = tooltip;
            }
        }
    });

    tooltip.showTimeout = null;

    // Gestion du long press sur mobile
    dojo.connect($(id), 'touchstart', (evt) => {
        if (isMobile) {
            longPressTimer = setTimeout(() => {
                tooltip.open($(id));
            }, 500); // 500ms = temps pour considérer un long press
        }
    });

    dojo.connect($(id), 'touchend', (evt) => {
        if (isMobile) {
            clearTimeout(longPressTimer);
        }
    });

    dojo.connect($(id), 'touchmove', (evt) => {
        if (isMobile) {
            clearTimeout(longPressTimer); // Annule le long press si l'utilisateur glisse son doigt
        }
    });

    // Gestion normale des tooltips sur PC
    dojo.connect($(id), 'mouseenter', (evt) => {
        evt.stopPropagation();

        if (!this._helpMode && !this._dragndropMode) {
            if (isMobile) return; // Bloque l'affichage des tooltips sur mobile hors help mode

            if (tooltip.showTimeout != null) 
                clearTimeout(tooltip.showTimeout);

            tooltip.showTimeout = setTimeout(() => {
                if ($(id)) 
                    tooltip.open($(id));
            }, config.delay);
        }
    });

    dojo.connect($(id), 'mouseleave', (evt) => {
        evt.stopPropagation();
        if (!this._helpMode && !this._dragndropMode) {
            tooltip.close();
            if (tooltip.showTimeout != null) 
                clearTimeout(tooltip.showTimeout);
        }
    });
},


destroyTooltip(elem) {
    if (this.tooltips[elem.id]) {
    clearTimeout(this.tooltips[elem.id].showTimeout);
    this.tooltips[elem.id].close();
    this.tooltips[elem.id].destroy();
    delete this.tooltips[elem.id];
    }
},

destroy(elem, delayRemove = false) {
    this.destroyTooltip(elem);
    this.empty(elem);
    if(!delayRemove) 
    elem.remove();
},

empty(container) {

    container = $(container);
    container.childNodes.forEach((node) => {
    //!! destroy node makes gap in LOOP because of removing them
    this.destroy(node,true);
    });
    container.childNodes.forEach((node) => {
    node.remove();
    });
    container.innerHTML = '';
},








});             
});