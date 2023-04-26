<template>
    <div id="l-siteWidth">
        <!--新規ユーザー登録画面-->
        <section class="p-register">
            <h2 class="c-ornament p-register__title">
                <span class="c-ornament__border p-register__border">
                    Register
                </span>
            </h2>
            <!--ユーザー登録用フォーム-->
            <form class="c-form p-register__form" @submit.prevent="register">
                <!--メールアドレス入力部分-->
                <label for="email" class="c-label p-register__label">
                    メールアドレス
                </label>
                <input id="email" type="text" name="email" class="c-input p-register__input"
                        v-model="registerForm.email" placeholder="step@example.com"
                        :class="(registerErrors !== null && registerErrors.email) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="registerErrors" class="c-error p-register__error">
                        <ul v-if="registerErrors.email" class="c-error__ul p-register__errorUl">
                            <li class="c-error__list p-register__errorList" v-for="msg in registerErrors.email" :key="msg">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                {{ msg }}
                            </li>
                        </ul>
                </div>
                <!--パスワード入力部分-->
                <label for="password" class="c-label p-register__label">
                    パスワード
                    <span class="c-label__passwordIcon" @click.prevent="changeInputType">
                        <i :class="eyeStyle" ></i>
                    </span>
                </label>
                <input id="password" :type="inputType" name="password" class="c-input p-register__input"
                       v-model="registerForm.password" placeholder="8文字以上のパスワード"
                       :class="(registerErrors !== null && registerErrors.password) ? 'c-input--error' : ''">
                <!--パスワード再入力部分-->
                <label for="password_confirmation" class="c-label p-register__label">パスワード（再入力）</label>
                <input id="password_confirmation" :type="inputType" name="password" class="c-input p-register__input"
                       v-model="registerForm.password_confirmation" placeholder="8文字以上のパスワード"
                       :class="(registerErrors !== null && registerErrors.password) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="registerErrors" class="c-error p-register__error">
                    <ul v-if="registerErrors.password" class="c-error__ul p-register__errorUl">
                        <li class="c-error__list p-register__errorList" v-for="msg in registerErrors.password" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ msg }}
                        </li>
                    </ul>
                </div>
                <!--新規登録ボタン部分-->
                <div class="c-submit p-register__submit">
                    <button type="submit" class="c-button p-register__button">登録する</button>
                </div>
                <!--ログインページへのリンク-->
                <p class="p-register__para">
                    <RouterLink class="p-register__link" to="/login">
                        ログインはこちらから
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
                registerForm : {
                    email : '',
                    password : '',
                    password_confirmation : '',
                },
                inputType:'password',
                eyeStyle:'fa-solid fa-eye',
            }
        },
        methods: {
            //ユーザー新規登録処理
            async register(){

                await this.$store.dispatch('auth/register', this.registerForm)

                //apiStatusがtrue（201CREATED 新規登録成功）であれば、
                if(this.apiStatus) {
                    //マイページへ遷移する
                    this.$router.push('/mypage')
                }
            },
            //バリデーションエラーを空にする
            clearError () {
                     this.$store.commit('auth/setRegisterErrorMessages', null)
            },
            //inputのtype（パスワード入力部分・パスワード再入力部分）を変更する
            changeInputType(){
                if(this.inputType === 'password'){
                    this.inputType = 'text'
                    this.eyeStyle = 'fa-sharp fa-solid fa-eye-slash'
                }else{
                    this.inputType = 'password'
                    this.eyeStyle = 'fa-solid fa-eye'
                }
            }
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
            //registerErrorMessagesステートを参照する
            registerErrors () {
                 return this.$store.state.auth.registerErrorMessages
            }
        },
        created () {
            //create1時にバリデーションエラーを初期化する
             this.clearError()
        }
    }

</script>
