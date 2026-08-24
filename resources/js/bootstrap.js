import 'bootstrap';

/**
 * Axios HTTP library.
 */
import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common[
    'X-Requested-With'
] = 'XMLHttpRequest';