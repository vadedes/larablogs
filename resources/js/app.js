import './bootstrap';
import Search from './live-search';

const searchIcon = document.querySelector('.header-search-icon');

if(searchIcon) {
    new Search();
}
