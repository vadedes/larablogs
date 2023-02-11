import './bootstrap';
import Search from './live-search';
import Chat from './chat';
import Profile from './profile'

const searchIcon = document.querySelector('.header-search-icon');
const chatIcon = document.querySelector('.header-chat-icon');
const profileNav = document.querySelector('.profile-nav');

if(searchIcon) {
    new Search();
}

if(chatIcon) {
    new Chat();
}

if(profileNav) {
    new Profile();
}
