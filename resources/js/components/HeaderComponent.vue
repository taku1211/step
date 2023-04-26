<template>
    <!--ヘッターー部分-->
    <header id="l-header">
        <div class="p-header">
            <h1 class="c-logo p-header__logo">
                    <a href="#" class="p-header__logoLink" v-if="path === '/'" @click.prevent="returnTop">
                        <img src="../../image/header_logo.png" alt="サイトロゴ「STEP」">
                    </a>
                    <RouterLink class="p-header__logoLink" to="/" v-else>
                        <img src="../../image/header_logo.png" alt="サイトロゴ「STEP」">
                    </RouterLink>
            </h1>
            <!--レスポンシブ対応（width:767px未満）の場合ハンバーガーメニューを表示-->
            <!--ただし、エラー画面の場合は、ハンバーガーメニュー非表示-->
            <ul class="p-header__humburgerMenu" v-if="path !== '/404' && path !== '/500' && path !== '/419'">
                <li class="p-header__icon" v-if="!openHeaderMenu" @click="openMenu"><i class="fa-solid fa-bars" ></i></li>
                <li class="p-header__icon" v-else><i class="fa-solid fa-xmark" ></i></li>
            </ul>
            <!--それ以外の場合はメニューを表示-->
            <!--ただし、エラー画面の場合は、メニュー非表示-->
            <div class="p-header__flexRight" v-if="path !== '/404' && path !== '/500' && path !== '/419'">
                <nav class="c-nav p-header__nav">
                    <ul class="c-nav__menu p-header__menu">
                        <li class="p-header__list">
                            <a href="#" class="p-header__link" v-if="path === '/'" @click.prevent="returnTop">
                                Top
                            </a>
                            <RouterLink class="p-header__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="p-header__list">
                            <a href="#" v-scroll-to="'#about'" class="p-header__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="p-header__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="p-header__list">
                            <RouterLink class="p-header__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="p-header__list" v-if="isLogin">
                            <RouterLink class="p-header__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="p-header__list" v-else>
                            <RouterLink class="p-header__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="p-header__list" v-if="isLogin">
                            <RouterLink class="p-header__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="p-header__list" v-else>
                            <RouterLink class="p-header__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="p-header__list" v-if="isLogin">
                            <a class="p-header__link" @click="logout">Logout</a>
                        </li>

                    </ul>
                </nav>

            </div>
        </div>
        <!--Sessionメッセージ表示用のコンポーネントの読み込み-->
        <message-component></message-component>
        <!--ハンバーガーメニュー用のコンポーネントの読み込み-->
        <transition name="fadeSoon">
            <overview-component v-if="openHeaderMenu" @closeMenu="closeMenu"></overview-component>
        </transition>
        <transition name="top">
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
            //ログアウト処理
            async logout() {
                await this.$store.dispatch('auth/logout')

                //apiStatusがtrue（200OK）であれば
                if (this.apiStatus) {
                    //ログインページへ遷移する
                     this.$router.push('/login')
                }
            },
            //ページ上部へ戻る
            returnTop(){
                window.scroll({
                    top:0,
                    behavior:'smooth',
                })
            },
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
