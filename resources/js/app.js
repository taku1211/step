import './bootstrap'
import Vue from 'vue'
import VueScrollTo from 'vue-scrollto'

Vue.use(VueScrollTo,{
    offset: -130 //vue-scrollを使用した際にヘッダーの高さ130px分マイナスしてスクロールさせる
})

//Jqueryの読み込み・使用宣言
import jQuery from 'jquery'
global.jquery = jQuery
global.$ = jQuery
window.$ = window.jQuery = require('jquery')

//router.jsで定義したルーティングをインポートする
import router from './router';

//Vuexの定義ファイルをインポートする
import store from './store'

//ルートコンポーネントをインポートする
import IndexComponent from "./components/IndexComponent.vue";


const createApp = async () => {
    //インスタンス生成前にログイン状態を維持する
    await store.dispatch('auth/currentUser')

    //インスタンスの生成処理
    new Vue({
        el: '#app',
        router,
        store,
        components: {
            IndexComponent,
        }
    })
};

createApp()

//=================================================
//Jquery処理
//=================================================

//スクロールした際に、要素がFadeInするアニメーション
$(window).on('scroll load', function(){
    let scroll = $(this).scrollTop();
    let windowHeight = $(window).height();
    $('.u-jsFadeIn').each(function(){
        let domHeight = $(this).offset().top;
        if(scroll > domHeight - windowHeight + windowHeight / 3){
            $(this).addClass('u-jsFadeIn--active')
        }
    })
})
