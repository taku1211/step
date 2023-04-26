<template>
    <div id="l-siteWidth">
        <!--419 unknownStatus（認証エラー）ページ-->
        <section class="c-errorView p-419Error">
            <p class="c-errorView__para p-419Error__para">
                セッションエラーが発生しました。
            </p>
            <p class="c-errorView__para p-419Error__para" >
                <a href="" class="c-errorView__link p-419Error__link" @click.prevent="refreshToken">
                    ログイン画面へ戻るにはこちらから。
                </a>
            </p>
        </section>
    </div>
</template>

<script>
    export default{

        methods: {
            //トークンをリセット＋ログアウト処理を行い、loginページへ遷移させる
            async refreshToken(){
                await axios.get('/api/token/refresh')
                this.$store.commit('auth/setUser', null)
                await this.$store.dispatch('auth/reset')
                this.$router.push('/login')
            },
        }
    }
</script>

