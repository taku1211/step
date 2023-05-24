<template>
    <div id="l-body">

        <!--Header部分の読み込み-->
        <header-component :path="pathName"></header-component>

        <!--Vue-routerで動的に変化するコンテンツ部分-->
        <main id="l-main">
            <router-view></router-view>
        </main>

        <!--ページ右下に表示されるページトップへ戻るボタン-->
        <transition name="c-fade">
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
            visibleReturnTopButton:false,
            width: window.innerWidth
        }
    },
    computed: {
        //エラーコードを取得
        errorCode(){
            return this.$store.state.error.code
        }
    },
    methods: {
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
    },
    created() {
        //ページ表示時にスクロール量を検知
        window.addEventListener("scroll", this.handleScroll)
    }
}

</script>
