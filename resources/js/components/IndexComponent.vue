<template>
    <div id="l-body" :class="this.class">
        <!--ページ遷移時のアニメーション部分-->
        <div :class="this.animationClass">
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
            <div :class="this.animationClassElement"></div>
        </div>

        <!--Header部分の読み込み-->
        <header-component :path="pathName"></header-component>

        <!--Vue-routerで動的に変化するコンテンツ部分-->
        <main id="l-main">
            <router-view></router-view>
        </main>

        <!--ページ右下に表示されるページトップへ戻るボタン-->
        <transition name="fadeSoon">
            <div class="c-returnButton" v-show="visibleReturnTopButton">
                <i class="fa-solid fa-circle-arrow-up c-returnButton__icon" @click="returnTop"></i>
            </div>
        </transition>

        <!--Footer部分の読み込み-->
        <footer-component v-if="pathName !== '/login' && pathName !== '/register' && pathName !== '/404'
        && pathName !=='/500' && pathName !== '/419'" :path="pathName">
        </footer-component>

    </div>
</template>

<script>
//Header・Footer・各エラーコードのimport
import HeaderComponent from './HeaderComponent.vue'
import FooterComponent from './FooterComponent.vue'
import { INTERNAL_SERVER_ERROR,NOT_FOUND,REDIRECT,unknown_status, UNAUTHORIZED } from '../util'

export default {
    components: {
        HeaderComponent,
        FooterComponent,
    },
    data: function(){
        return {
            pathName: null,
            isOpen: true,
            class: 'u-open',
            animationClass: 'u-animation',
            animationClassElement: 'u-animation__element',
            visibleReturnTopButton:false,
        }
    },
    computed: {
        //エラーコードを取得
        errorCode(){
            return this.$store.state.error.code
        }
    },
    methods: {
        //ページ遷移時のアニメーションの終了メソッド//
        close(){
            this.class = 'u-close'
            this.animationClass = ''
            this.animationClassElement = ''
        },
        //ページ遷移時のアニメーションの開始メソッド
        open(){
            this.class = 'u-open'
            this.animationClass = 'u-animation'
            this.animationClassElement = 'u-animation__element'
        },
        //ページの一番上へスクロールで戻る
        returnTop(){
            window.scroll({
                top:0,
                behavior:'smooth',
            })
        },
        //ページトップへ戻るボタンの表示・非表示をスクロール位置で切り替える
        handleScroll() {
          this.scrollY = window.scrollY

          //ボタンが非表示の場合
          if (!this.visibleReturnTopButton) {
                //縦方向のスクロール量が上から200を超えたら
                if(window.scrollY > 200){
                    //ボタンを表示させる
                    this.visibleReturnTopButton = true
                }
          //ボタンがすでに表示されている場合
          }else if(this.visibleReturnTopButton) {
                //縦方向のスクロール量が上から200未満になったら、
                if(window.scrollY < 200)
                    //ボタンを非表示にする
                    this.visibleReturnTopButton = false
          }
        }
    },
    watch: {
        //errorCodeの監視
        errorCode: {
            async handler(val){
                if(val === INTERNAL_SERVER_ERROR){
                    this.$router.push('/500')
                }else if(val === unknown_status){
                    this.$router.push('/419')
                }else if(val === NOT_FOUND){
                    this.$router.push('/*')
                }else if(val === REDIRECT){
                    this.$router.push('/*')
                }else if(val === UNAUTHORIZED){
                    this.$router.push('/*')
                }
            },
            immediate:true
        },
        //routeオブジェクトの監視
        $route: {
                async handler () {
                    this.$store.commit('error/setCode', null)
                    this.pathName = location.pathname
                },
                immediate: true
        },
        //isOpenメソッドの監視
        isOpen:{
            handler(){
                this.isOpen ? this.open() : this.close()
            }
        }
    },
    created() {
        //ページ表示時にスクロール量を検知
        window.addEventListener("scroll", this.handleScroll)
    },
    mounted () {
        //ナビゲーション確立前の処理
        this.$router.beforeEach((to, from, next) => {
            //アニメーションの開始メソッドをfalseにする
            this.isOpen = false;
            next();
        })
        //ナビゲーション確立後の処理
        this.$router.afterEach((to, from, next) => {
            //0/8秒間、watchで監視しているisOpenをtrueにし、open()メソッドを実行
            setTimeout( () => {
                this.isOpen = true;
            }, 800);
        })
    },
}

</script>
