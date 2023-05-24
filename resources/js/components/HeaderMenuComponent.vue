<template>
    <!--ハンバーガーメニュ-->
    <div class="c-headerMenu">
        <div class="c-headerMenu__return">
            <span class="c-headerMenu__icon" @click="$emit('closeMenu')">✕</span>
        </div>
        <nav class="c-nav c-nav--responsive">
            <ul class="c-nav__menu c-nav__menu--responsive">
                        <li class="c-nav__list c-nav__list--responsive">
                            <a href="#" class="c-nav__link" v-if="path === '/'" @click.prevent="returnTop(); $emit('closeMenu')">
                                Top
                            </a>
                            <RouterLink class="c-nav__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive">
                            <a href="#" @click="$emit('closeMenu')" v-scroll-to="'#about'" class="c-nav__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="c-nav__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive">
                            <RouterLink class="c-nav__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive" v-else>
                            <RouterLink class="c-nav__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive" v-if="isLogin">
                            <RouterLink class="c-nav__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive" v-else>
                            <RouterLink class="c-nav__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="c-nav__list c-nav__list--responsive" v-if="isLogin">
                            <a class="c-nav__link" @click="logout();$emit('closeMenu') ">Logout</a>
                        </li>
            </ul>
        </nav>
        <div class="c-headerMenu__footer">
            <!--黒色の余白を創っている部分（文字等はなし）-->
        </div>

    </div>
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
        watch: {
            //$routeの監視
            $route: {
                async handler () {
                    //$routeが変更されたら、ハンバーガメニューを閉じる
                    this.$emit('closeMenu')
                },
            },
        }
    }
  </script>
