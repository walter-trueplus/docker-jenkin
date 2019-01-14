import {dispatch, subscribe, fire, listen} from '..'

describe('Event Testing', () => {
    it('Subscribe and dispatch events', () => {
        let observer = jest.fn();

        // Before subscribe
        dispatch('event');
        expect(observer.mock.calls.length).toBe(0);

        // Subscribe to event
        subscribe('event', observer);
        dispatch('event', 'data');
        expect(observer.mock.calls.length).toBe(1);
        expect(observer.mock.calls[0][0]).toBe('data');

        // Dispatch other event
        dispatch('other');
        expect(observer.mock.calls.length).toBe(1);

        // Redispatch event
        dispatch('event', 'data');
        expect(observer.mock.calls.length).toBe(2);
        expect(observer.mock.calls[0][0]).toBe('data');
    });

    it ('Fire and listen', () => {
        let observer = jest.fn();
        listen('event', observer);
        fire('event', 'data');
        expect(observer.mock.calls.length).toBe(1);
        expect(observer.mock.calls[0][0]).toBe('data');
    });
});
