<template>
    <!--ヘッターー部分-->
    <header id="l-header">
        <div class="c-header">
            <h1 class="c-logo c-logo--header">
                    <a href="#" class="c-logo__link" v-if="path === '/'" @click.prevent="returnTop">
                        <img src="../../image/header_logo.png" alt="サイトロゴ「STEP」">
                    </a>
                    <RouterLink class="c-logo__link" to="/" v-else>
                        <img src="../../image/header_logo.png" alt="サイトロゴ「STEP」">
                    </RouterLink>
            </h1>
            <!--レスポンシブ対応（width:767px未満）の場合ハンバーガーメニューを表示-->
            <!--ただし、エラー画面の場合は、ハンバーガーメニュー非表示-->
            <ul class="c-header__humburgerMenu" v-if="path !== '/404' && path !== '/500' && path !== '/419'">
                <li class="c-header__icon" v-if="!openHeaderMenu" @click="openMenu"><i class="fa-solid fa-bars" ></i></li>
                <li class="c-header__icon" v-else><i class="fa-solid fa-xmark" ></i></li>
            </ul>
            <!--それ以外の場合はメニューを表示-->
            <!--ただし、エラー画面の場合は、メニュー非表示-->
            <div class="c-header__flexRight" v-if="path !== '/404' && path !== '/500' && path !== '/419'">
                <nav class="c-nav">
                    <ul class="c-nav__menu">
                        <li class="c-nav__list">
                            <a href="#" class="c-nav__link" v-if="path === '/'" @click.prevent="returnTop">
                                Top
                            </a>
                            <RouterLink class="c-nav__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="c-nav__list">
                            <a href="#" v-scroll-to="'#about'" class="c-nav__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="c-nav__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="c-nav__list">
                            <RouterLink class="c-nav__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="c-nav__list" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="c-nav__list" v-else>
                            <RouterLink class="c-nav__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="c-nav__list" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="c-nav__list" v-else>
                            <RouterLink class="c-nav__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="c-nav__list" v-if="isLogin">
                            <a class="c-nav__link" @click="logout">Logout</a>
                        </li>

                    </ul>
                </nav>

            </div>
        </div>
        <!--Sessionメッセージ表示用のコンポーネントの読み込み-->
        <message-component></message-component>
        <!--ハンバーガーメニュー用のコンポーネントの読み込み-->
        <transition name="c-fade">
            <overview-component v-if="openHeaderMenu" @closeMenu="closeMenu"></overview-component>
        </transition>
        <transition name="c-slideDown">
            <headermenu-component v-if="openHeaderMenu" @closeMenu="closeMenu" :path="path"></headermenu-component>
        </transition>
    </header>
</template>

<script>
    import MessageComponent from './MessageComponent.vue'
    import OverviewComponent from './OverViewComponent.vue'
    import HeadermenuComponent from './HeaderMenuComponent.vue'

    export default {
        components: {
            MessageComponent,
            OverviewComponent,
            HeadermenuComponent,
        },
        props:{
            path:{
                Type:String,
                default:null,
            }
        },
        data: function(){
            return{
                openHeaderMenu: false,
            }
        },
        methods: {
            //ハンバーガーメニューを拓く
            openMenu(){
                this.openHeaderMenu = true
            },
            //ハンバーガーメニューを閉じる
            closeMenu(){
                this.openHeaderMenu = false
            }
        },
        computed: {
            //ログイン状態の真偽判断
            isLogin() {
                return this.$store.getters['auth/check']
            },
            //ログインしているユーザーのEmailを取得
            userEmail() {
                return this.$store.getters['auth/email']
            },
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
        },
    }

</script>
