<template>
    <!--フッター部分-->
    <footer id="l-footer">
        <div class="p-footer">
            <h2 class="c-logo p-footer__logo">
                <img src="../../image/header_logo.png" alt="サイトロゴ「STEP」">
            </h2>

            <div class="p-footer__guidLink">
                <nav class="c-nav p-footer__nav">
                    <ul class="c-nav__menu p-footer__menu">
                        <li class="p-footer__list">
                            <a href="#" class="p-header__link" v-if="path === '/'" @click.prevent="returnTop">
                                Top
                            </a>
                            <RouterLink class="p-header__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="p-footer__list">
                            <a href="#" v-scroll-to="'#about'" class="p-header__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="p-header__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="p-footer__list">
                            <RouterLink class="p-footer__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="p-footer__list" v-if="isLogin">
                            <RouterLink class="p-footer__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="p-footer__list" v-else>
                            <RouterLink class="p-footer__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="p-footer__list" v-if="isLogin">
                            <RouterLink class="p-footer__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="p-footer__list" v-else>
                            <RouterLink class="p-footer__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="p-footer__list" v-if="isLogin">
                            <a class="p-footer__link" @click="logout">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <p class="p-footer__copyRight">
                Copyright © STEP.   All Rights   Reserved
            </p>

        </div>
    </footer>
</template>

<script>
    export default {
        props:{
            path:{
                Type:String,
                default:null,
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
