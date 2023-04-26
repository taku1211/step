<template>
    <!--ハンバーガーメニュ-->
    <div class="p-headerMenu">
        <div class="p-headerMenu__return">
            <span class="p-headerMenu__icon" @click="$emit('closeMenu')">✕</span>
        </div>
        <nav class="p-headerMenu__nav">
            <ul class="c-nav__menu p-headerMenu__menu">
                        <li class="p-headerMenu__list">
                            <a href="#" class="p-headerMenu__link" v-if="path === '/'" @click.prevent="returnTop(); $emit('closeMenu')">
                                Top
                            </a>
                            <RouterLink class="p-headerMenu__link" to="/" v-else>
                                Top
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list">
                            <a href="#" @click="$emit('closeMenu')" v-scroll-to="'#about'" class="p-headerMenu__link" v-if="path === '/'">
                                About
                            </a>
                            <RouterLink class="p-headerMenu__link" v-scroll-to="'#about'" to="/#about" v-else>
                                About
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list">
                            <RouterLink class="p-headerMenu__link" to="/index">
                                All STEP
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list" v-if="isLogin">
                            <RouterLink class="p-headerMenu__link" to="/mypage">
                                Mypage
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list" v-else>
                            <RouterLink class="p-headerMenu__link" to="/login">
                                Login
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list" v-if="isLogin">
                            <RouterLink class="p-headerMenu__link" to="/setting">
                                Setting
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list" v-else>
                            <RouterLink class="p-headerMenu__link" to="/register">
                                Register
                            </RouterLink>
                        </li>
                        <li class="p-headerMenu__list" v-if="isLogin">
                            <a class="p-headerMenu__link" @click="logout();$emit('closeMenu') ">Logout</a>
                        </li>
            </ul>
        </nav>
        <div class="p-headerMenu__footer">
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
        methods:{
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
