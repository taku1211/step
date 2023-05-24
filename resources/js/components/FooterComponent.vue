<template>
    <!--フッター部分-->
    <footer id="l-footer">
        <div class="c-footer">
            <h2 class="c-logo c-logo--footer">
                <img src="../../image/footer_logo.png" alt="サイトロゴ「STEP」">
            </h2>

            <div class="c-footer__guidLink">
                <nav class="c-nav">
                    <ul class="c-nav__menu c-nav__menu--footer">
                        <li class="c-nav__list c-nav__list--footer">
                            <a href="#" class="c-nav__link" v-if="path === '/'" @click.prevent="returnTop">
                                Top
                            </a>
                            <RouterLink class="c-nav__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer">
                            <a href="#" v-scroll-to="'#about'" class="c-nav__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="c-nav__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer">
                            <RouterLink class="c-nav__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer" v-else>
                            <RouterLink class="c-nav__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer" v-else>
                            <RouterLink class="c-nav__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--footer" v-if="isLogin">
                            <a class="c-nav__link" @click="logout">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <p class="c-footer__copyRight">
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
