import {Subject} from "rxjs";

let events = {};

/**
 * Get a subject singleton
 *
 * @param {String} name
 * @returns {Subject}
 */
function getSubject(name) {
    if (!events.hasOwnProperty(name)) {
        events[name] = new Subject();
    }
    return events[name];
}

/**
 * Subscribe to an event with name
 *
 * @param {String} name
 * @param {Function} observer
 */
export function subscribe(name, observer) {
    getSubject(name).subscribe(observer);
}

/**
 * Dispatch event with event data
 *
 * Data will be passed to observer functions
 *
 * @param {String} name
 * @param {any} data
 */
export function dispatch(name, data) {
    getSubject(name).next(data);
}

// Export Aliases
export {
    subscribe as listen,
    dispatch as fire,
};
