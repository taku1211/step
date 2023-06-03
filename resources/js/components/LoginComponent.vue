<template>
    <div id="l-main--siteWidth">
        <!--ログイン画面-->
        <section>
            <h2 class="c-ornament">
                <span class="c-ornament__border">
                    Login
                </span>
            </h2>
            <!--ログイン用フォーム-->
            <form class="c-form" @submit.prevent="login">
                <!--メールアドレス入力部分-->
                <label for="email" class="c-label c-label--marginl">
                    メールアドレス
                </label>
                <input id="email" type="text" name="email" class="c-input"
                       v-model="loginForm.email" placeholder="step@example.com"
                       :class="(loginErrors !== null && loginErrors.email) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="loginErrors" class="c-error">
                        <ul v-if="loginErrors.email" class="c-error__ul">
                            <li class="c-error__list"  v-for="msg in loginErrors.email" :key="msg">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                {{ msg }}
                            </li>
                        </ul>
                </div>
                <!--パスワード入力部分-->
                <label for="password" class="c-label c-label--marginl">
                    パスワード
                        <span class="c-label__passwordIcon" @click.prevent="changeInputType">
                            <i :class="eyeStyle" ></i>
                        </span>
                </label>
                <input id="password" :type="inputType" name="password" class="c-input" :class="(loginErrors !== null && loginErrors.password) ? 'c-input--error' : ''"
                       v-model="loginForm.password" placeholder="8文字以上のパスワード">
                <!--バリデーションエラー表示部分-->
                <div v-if="loginErrors" class="c-error">
                        <ul v-if="loginErrors.password" class="c-error__ul">
                            <li class="c-error__list" v-for="msg in loginErrors.password" :key="msg">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                {{ msg }}
                            </li>
                        </ul>
                </div>
                <!--ログインボタン部分-->
                <div class="c-submit">
                    <button type="submit" class="c-button c-button--orange">ログイン</button>
                </div>
                <!--パスワード再設定用ページへのリンク-->
                <p>
                    <RouterLink class="c-authPageLink" to="/password/forgot">
                        パスワードの再設定はこちらから
                    </RouterLink>
                </p>
            </form>
        </section>
    </div>
</template>

<script>

    export default {
        data: function(){
            return {
                loginForm : {
                    email : '',
                    password : '',
                },
                inputType:'password',
                eyeStyle:'fa-solid fa-eye',
            }
        },
        methods: {
            //ログイン処理
            async login(){
                await this.$store.dispatch('auth/login', this.loginForm)

                //apiStatusがtrue（200OK ログイン成功）であれば、
                if(this.apiStatus) {
                    //マイページへ遷移する
                    this.$router.push('/mypage')
                }
            },
            //バリデーションエラーを空にする
            clearError () {
                     this.$store.commit('auth/setLoginErrorMessages', null)
            },
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
            //loginErrorMessagesステートを参照する
            loginErrors () {
                 return this.$store.state.auth.loginErrorMessages
            }
        },
        created () {
            //create1時にバリデーションエラーを初期化する
             this.clearError()
        }
    }

</script>
