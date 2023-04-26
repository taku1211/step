<template>
    <div id="l-siteWidth">
        <!--パスワード再設定画面-->
        <section class="p-resetPassword">
            <h2 class="c-ornament p-resetPassword__title">
                <span class="c-ornament__border c-resetPassword__border">
                    パスワード再設定
                </span>
            </h2>
            <!--パスワード再設定用フォーム-->
            <form class="c-form p-resetPassword__form" @submit.prevent="resetPassword">
                <!--メールアドレス入力部分-->
                <label for="email" class="c-label p-resetPassword__label">
                    メールアドレス
                </label>
                <input id="email" type="text" name="email" class="c-input p-resetPassword__input p-resetPassword__input--invalid" disabled
                       v-model="resetForm.email" placeholder="step@example.com" :class="(updateErrors !== null && updateErrors.email) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error p-resetPassword__error">
                    <ul v-if="updateErrors.email" class="c-error__ul p-resetPassword__errorUl">
                        <li class="c-error__list p-resetPassword__errorList"  v-for="msg in updateErrors.email" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ msg }}
                        </li>
                    </ul>
                </div>

                <!--パスワード入力部分-->
                <label for="password" class="c-label p-resetPassword__label">
                    パスワード
                    <span class="c-label__passwordIcon" @click.prevent="changeInputType">
                            <i :class="eyeStyle" ></i>
                    </span>
                </label>
                <input id="password" :type="inputType" name="password" class="c-input p-resetPassword__input"
                       v-model="resetForm.password" placeholder="8文字以上のパスワード"
                       :class="(updateErrors !== null && updateErrors.password) ? 'c-input--error' : ''">
                <!--パスワード再入力部分-->
                <label for="password_confirmation" class="c-label p-resetPassword__label">
                    パスワード（再入力）
                </label>
                <input id="password_confirmation" :type="inputType" name="password" class="c-input p-resetPassword__input"
                       v-model="resetForm.password_confirmation" placeholder="8文字以上のパスワード"
                       :class="(updateErrors !== null && updateErrors.password) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error p-resetPassword__error">
                    <ul v-if="updateErrors.password" class="c-error__ul p-resetPassword__errorUl">
                        <li class="c-error__list p-resetPassword__errorList"  v-for="msg in updateErrors.password" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ msg }}
                        </li>
                    </ul>
                    <ul v-if="updateErrors && !updateErrors.email && !updateErrors.password" class="c-error__ul p-sendMail__errorUl">
                        <li class="c-error__list p-sendMail__errorList">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ updateErrors }}
                        </li>
                    </ul>
                </div>

                <!--再設定ボタン部分-->
                <div class="c-submit p-resetPassword__submit">
                    <button type="submit" class="c-button p-resetPassword__button p-resetPassword__button--orange">
                        再設定する
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
    export default {
        data: function(){
            return {
                resetForm : {
                    email : this.$route.query.email || "",
                    password : '',
                    password_confirmation : '',
                    token:this.$route.query.token || "",
                },
                inputType:'password',
                eyeStyle:'fa-solid fa-eye',
            }
        },
        methods: {
            //パスワード再設定処理
            async resetPassword(){
                await this.$store.dispatch('auth/resetPassword', this.resetForm)

                //apiStatusがtrue（200OK 再設定成功）であれば、
                if(this.apiStatus) {
                    //ログインページへ遷移する
                    this.$router.push('/login')
                }
            },
            //validationエラーを空にする
            clearError () {
                     this.$store.commit('auth/setUpdatePasswordErrorMessages', null)
            },
            //inputのtypeを変更する
            changeInputType(){
                if(this.inputType === 'password'){
                    this.inputType = 'text'
                    this.eyeStyle = 'fa-sharp fa-solid fa-eye-slash'
                }else{
                    this.inputType = 'password'
                    this.eyeStyle = 'fa-solid fa-eye'
                }
            },

        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
            //registerErrorMessagesステートを参照する
            updateErrors () {
                 return this.$store.state.auth.updatePasswordErrorMessages
            }
        },
        created () {
            //create1時にバリデーションエラーを初期化する
             this.clearError()
        }

    }

</script>
