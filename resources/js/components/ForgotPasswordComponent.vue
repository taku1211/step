<template>
    <div id="l-siteWidth">
        <!--パスワード再設定メール送信画面-->
        <div class="p-sendMail">
            <h2 class="c-ornament p-sendMail__title">
                <span class="c-ornament__border p-sendMail__order">
                    パスワード再設定
                </span>
            </h2>

            <form class="c-form p-sendMail__form" @submit.prevent="sendResetMail">
                <label for="email" class="c-label p-sendMail__label">登録しているEmailを入力してください。</label>
                <input id="email" type="text" name="email" class="c-input p-sendMail__input"
                       v-model="resetForm.email" placeholder="step@example.com" :class="(resetErrors !== null) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="resetErrors" class="c-error p-sendMail__error">
                    <ul v-if="resetErrors.email" class="c-error__ul p-sendMail__errorUl">
                        <li class="c-error__list p-sendMail__errorList" v-for="msg in resetErrors.email" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                                {{ msg }}
                        </li>
                    </ul>
                    <ul v-if="resetErrors && !resetErrors.email " class="c-error__ul p-sendMail__errorUl">
                        <li class="c-error__list p-sendMail__errorList">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ resetErrors }}
                        </li>
                    </ul>
                </div>

                <div class="c-submit p-sendMail__submit">
                    <button type="submit" class="c-button p-sendMail__button">
                        メールを送信
                    </button>
                </div>
                <!--ログイン画面へのリンク-->
                <p class="p-sendMail__para">
                    <RouterLink class="p-sendMail__link" to="/login">
                        ログインはこちらから
                    </RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data: function(){
            return {
                resetForm : {
                    email : '',
                }
            }
        },
        methods: {
            //パスワードリセット用のメールを送信する
            async sendResetMail(){

                await this.$store.dispatch('auth/sendResetMail', this.resetForm)

                if(this.apiStatus) {
                //apiStatusがtrue（200OK）であれば、
                    //ログインページへ遷移する
                    this.$router.push('/login')
                }
            },
            //validationエラーを空にする
            clearError () {
                     this.$store.commit('auth/setResetErrorMessages', null)
            },
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
            //resetErrorMessagesステートを参照する
            resetErrors () {
                 return this.$store.state.auth.resetErrorMessages
            }
        },
        created () {
            //ページcreate時にバリデーションエラーをリセットする
             this.clearError()
        }
    }

</script>
