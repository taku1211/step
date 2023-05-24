<template>
    <div id="l-main--siteWidth">
        <!--パスワード再設定画面-->
        <section class="c-authPage">
            <h2 class="c-ornament">
                <span class="c-ornament__border">
                    パスワード再設定
                </span>
            </h2>
            <!--パスワード再設定用フォーム-->
            <form class="c-form" @submit.prevent="resetPassword">
                <!--メールアドレス入力部分-->
                <label for="email" class="c-label c-label--marginl">
                    メールアドレス
                </label>
                <input id="email" type="text" name="email" class="c-input c-input--invalid" disabled
                       v-model="resetForm.email" placeholder="step@example.com" :class="(updateErrors !== null && updateErrors.email) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error">
                    <ul v-if="updateErrors.email" class="c-error__ul">
                        <li class="c-error__list"  v-for="msg in updateErrors.email" :key="msg">
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
                <input id="password" :type="inputType" name="password" class="c-input"
                       v-model="resetForm.password" placeholder="8文字以上のパスワード"
                       :class="(updateErrors !== null && updateErrors.password) ? 'c-input--error' : ''">
                <!--パスワード再入力部分-->
                <label for="password_confirmation" class="c-label c-label--marginl">
                    パスワード（再入力）
                </label>
                <input id="password_confirmation" :type="inputType" name="password" class="c-input"
                       v-model="resetForm.password_confirmation" placeholder="8文字以上のパスワード"
                       :class="(updateErrors !== null && updateErrors.password) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error">
                    <ul v-if="updateErrors.password" class="c-error__ul">
                        <li class="c-error__list"  v-for="msg in updateErrors.password" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ msg }}
                        </li>
                    </ul>
                    <ul v-if="updateErrors && !updateErrors.email && !updateErrors.password" class="c-error__ul">
                        <li class="c-error__list">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ updateErrors }}
                        </li>
                    </ul>
                </div>

                <!--再設定ボタン部分-->
                <div class="c-submit">
                    <button type="submit" class="c-button c-button--orange">
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
