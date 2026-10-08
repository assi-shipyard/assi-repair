import '@tabler/core/dist/css/tabler.min.css';
import '@tabler/icons-webfont/dist/tabler-icons.min.css';
import '@tabler/core/dist/css/tabler-flags.min.css';
import '@tabler/core/dist/css/tabler-payments.min.css';
import '@tabler/core/dist/css/tabler-vendors.min.css';
import * as tabler from '@tabler/core';
import './date_picker.js';

// Tabler 1.6 exposes bootstrap only as a module export; page scripts expect globals.
window.tabler = tabler;
window.bootstrap = tabler.bootstrap;
