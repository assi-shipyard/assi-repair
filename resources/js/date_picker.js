import { Calendar, time } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

const value_pattern = /^(\d{4}-\d{2}-\d{2})(?:[T ](\d{2}):(\d{2}))?/;

/*
 * Replaces native <input type="date|datetime-local"> with Vanilla Calendar Pro.
 * The submitted value keeps the server contract: "YYYY-MM-DD" for dates and
 * "YYYY-MM-DD HH:mm" for datetimes. Native `change` events are dispatched so
 * existing jQuery handlers and jQuery Validation keep working.
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
            onChangeToInput(self) {
                const date = self.context.selectedDates[0];
                const time = `${self.context.selectedHours}:${self.context.selectedMinutes}`;

                self.context.inputElement.value = date ? (with_time ? `${date} ${time}` : date) : '';
                self.context.inputElement.dispatchEvent(new Event('input', { bubbles: true }));
                self.context.inputElement.dispatchEvent(new Event('change', { bubbles: true }));

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
