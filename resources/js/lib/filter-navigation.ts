// Preserve the latest filter intent while debouncing search and cancel obsolete requests.
export function createFilterNavigation<T extends Record<string, string>>(
    initial: T,
    visit: (filters: T, replace: boolean) => () => void,
    delay = 350,
) {
    let filters = initial;
    let timer: ReturnType<typeof setTimeout> | undefined;
    let cancelRequest: (() => void) | undefined;

    function cancel() {
        if (timer !== undefined) clearTimeout(timer);
        timer = undefined;
        const previous = cancelRequest;
        cancelRequest = undefined;
        previous?.();
    }

    function change(values: Partial<T>, debounce = false) {
        cancel();
        filters = { ...filters, ...values };
        const send = () => {
            timer = undefined;
            cancelRequest = visit({ ...filters }, debounce);
        };
        if (debounce) timer = setTimeout(send, delay);
        else send();
    }

    return { change, cancel };
}
