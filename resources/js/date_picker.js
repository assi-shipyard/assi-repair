import { Calendar, time } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

const value_pattern = /^(\d{4}-\d{2}-\d{2})(?:[T ](\d{2}):(\d{2}))?/;
const month_names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

export function format_long_date(value, with_time) {
    const match = value_pattern.exec(value);

    if (!match) {
        return '';
    }

    const [year, month, day] = match[1].split('-').map(Number);
    const date_text = `${day} ${month_names[month - 1]} ${year}`;

    return with_time ? `${date_text}, ${match[2] ?? '00'}:${match[3] ?? '00'} WIB` : date_text;
}

function place_popup(popup, input) {
    const gap = 4;
    const rect = input.getBoundingClientRect();
    const popup_height = popup.offsetHeight;
    const popup_width = popup.offsetWidth;
    const fits_below = rect.bottom + gap + popup_height <= window.innerHeight;
    const top = fits_below || rect.top - gap - popup_height < 0 ? rect.bottom + gap : rect.top - gap - popup_height;
    const left = Math.max(gap, Math.min(rect.left, window.innerWidth - popup_width - gap));

    Object.assign(popup.style, { position: 'fixed', top: `${top}px`, left: `${left}px`, margin: '0' });
}

/*
 * The library mounts the popup on <body>, which puts it under the Bootstrap modal backdrop
 * and outside the modal focus trap (time inputs would lose focus). Mount it inside the modal
 * and position it against the viewport instead.
 */
function attach_to_modal(popup, input, modal) {
    if (popup.parentElement !== modal) {
        modal.appendChild(popup);
        popup.style.zIndex = '1060';

        const reposition = () => requestAnimationFrame(() => {
            if (!popup.hasAttribute('data-vc-calendar-hidden')) {
                place_popup(popup, input);
            }
        });
        modal.addEventListener('scroll', reposition, { passive: true });
        window.addEventListener('resize', reposition);
    }

    place_popup(popup, input);
}

/*
 * Replaces native <input type="date|datetime-local"> with Vanilla Calendar Pro.
 * The submitted value keeps the server contract: "YYYY-MM-DD" for dates and
 * "YYYY-MM-DD HH:mm" for datetimes. Native `change` events are dispatched so
 * existing jQuery handlers and jQuery Validation keep working.
 *
 * With data-date-display="long" the visible field shows the Indonesian long date
 * ("15 Oktober 2026, 08:00 WIB") while a hidden input that keeps the original
 * id/name carries the machine value. jQuery Validation forms must not ignore it:
 * ignore: ':hidden:not(.js-date-value)'.
 */
export function init_date_pickers(root = document) {
    root.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach((input) => {
        const with_time = input.type === 'datetime-local';
        const match = value_pattern.exec(input.value);
        const min_match = value_pattern.exec(input.min);
        const max_match = value_pattern.exec(input.max);

        // A text input is required: native date inputs do not accept a custom popup.
        input.type = 'text';
        input.autocomplete = 'off';
        input.placeholder = input.placeholder || (with_time ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD');
        if (match) {
            input.value = with_time ? `${match[1]} ${match[2] ?? '00'}:${match[3] ?? '00'}` : match[1];
        }

        const long_display = input.dataset.dateDisplay === 'long';
        let value_input = input;

        if (long_display) {
            value_input = document.createElement('input');
            value_input.type = 'hidden';
            value_input.name = input.name;
            value_input.id = input.id;
            value_input.className = 'js-date-value';
            value_input.value = input.value;
            value_input.required = input.required;

            input.removeAttribute('name');
            input.removeAttribute('required');
            input.id = value_input.id ? `${value_input.id}_display` : '';
            input.readOnly = true;
            input.classList.add('js-date-display');
            input.value = format_long_date(value_input.value, with_time);
            input.after(value_input);

            const label = value_input.id ? document.querySelector(`label[for="${value_input.id}"]`) : null;
            if (label) {
                label.htmlFor = input.id;
            }

            // Hidden inputs ignore form reset, so mirror it manually.
            input.form?.addEventListener('reset', () => {
                value_input.value = '';
                setTimeout(() => { input.value = ''; });
            });
        }

        const host_modal = input.closest('.modal');

        const calendar = new Calendar(input, {
            inputMode: true,
            positionToInput: 'auto',
            locale: 'id',
            selectedTheme: 'light',
            firstWeekday: 1,
            selectionTimeMode: with_time ? 24 : false,
            extensions: with_time ? [time] : [],
            selectedDates: match ? [match[1]] : [],
            selectedTime: match && with_time ? `${match[2] ?? '00'}:${match[3] ?? '00'}` : undefined,
            dateMin: min_match ? min_match[1] : undefined,
            dateMax: max_match ? max_match[1] : undefined,
            onShow(self) {
                if (host_modal) {
                    attach_to_modal(self.context.mainElement, input, host_modal);
                }
            },
            onChangeToInput(self) {
                const date = self.context.selectedDates[0];
                const time = `${self.context.selectedHours}:${self.context.selectedMinutes}`;

                const value = date ? (with_time ? `${date} ${time}` : date) : '';

                value_input.value = value;
                if (long_display) {
                    input.value = format_long_date(value, with_time);
                }
                value_input.dispatchEvent(new Event('input', { bubbles: true }));
                value_input.dispatchEvent(new Event('change', { bubbles: true }));

                if (date && !with_time) {
                    self.hide();
                }
            },
        });

        calendar.init();
    });
}

document.addEventListener('DOMContentLoaded', () => init_date_pickers());
window.init_date_pickers = init_date_pickers;
