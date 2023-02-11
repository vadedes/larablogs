import './bootstrap';
import Search from './live-search';
import Chat from './chat';

const searchIcon = document.querySelector('.header-search-icon');
const chatIcon = document.querySelector('.header-chat-icon');

if(searchIcon) {
    new Search();
}

if(chatIcon) {
    new Chat();
}
