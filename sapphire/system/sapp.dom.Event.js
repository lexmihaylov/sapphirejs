/**
 * @module sapp
 * @submodule dom
 */

/**
 * Event handler class. Helps cleanup the events after a component has been deleted
 * from the dom
 * @namespace sapp.dom
 * @param {object} component
 * @class Event
 * @constructor
 */
sapp.dom.Event = sapp.Class(function(component) {
    /**
     * Contains the object event connection
     * @property _events
     * @protected
     * @type {array}
     */
    this._events = [];
    /**
     * Contains the object reference
     * @property _component
     * @protected
     * @type {object}
     */
    this._component = component;
});

/**
 * Adds an event to the components context
 * @method add
 * @param {object} target the object to bind the event to
 * @param {string} type e.g. 'click', 'mousedown', 'keydown' etc.
 * @param {string} selector 
 * @param {object} data
 * @param {function} handler
 * @return {object} the object event connection object
 */
sapp.dom.Event.prototype.add = function(target, type, selector, data, handler) {
    // normalize method input
    if(typeof(target) == 'string') {
        handler = data;
        data = selector;
        selector = type;
        
        type = target;
        target = this._component;
    }
    
    if(typeof(selector) == 'object') {
        handler = data;
        data = selector;
        selector = undefined;
        
    } else if(typeof(selector) == 'function') {
        handler = selector;
        data = selector = undefined;
    }
    
    if(typeof(data) == 'function') {
        handler = data;
        data = undefined;
    }
    
    // create a unique function
    if(handler.guid) {
        handler.guid = undefined;
    }
    
    handler = $.proxy(handler, this._component);
    
    var eventObject = {
        type: type,
        target: target,
        handler: handler,
        selector: selector
    }
    
    this._events.push(eventObject);
    
    target.on(type, selector, data, handler);
    
    return eventObject;
};

/**
 * Unbinds an event
 * @method remove
 * @param {object} eventObject the object event connection object
 * @chainable
 */
sapp.dom.Event.prototype.remove = function(eventObject) {
    var index = this._events.indexOf(eventObject);
    var event = this._events[index];
    
    event.target.off(event.type, event.selector, event.handler);
    
    return this;
};

/**
 * Removes all events associated with an object
 * @method removeAll
 * @chainable
 */
sapp.dom.Event.prototype.removeAll = function() {
    for(var i = 0; i < this._events.length; i++)
        this.remove(this._events[i]);
    
    return this;
};