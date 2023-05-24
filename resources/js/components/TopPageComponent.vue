<template>
    <!--STEP TOPページ-->
    <div id="l-main--fullWidth">
        <!--ヒーローイメージ-->
        <section class="p-hero">
            <h2 class="p-hero__title">
                あなたの人生のSTEPを<span class="u-showMd u-showTab"><br></span>共有しよう
            </h2>
            <h3 class="p-hero__para">
                Share your steps in life
                <span class="u-showMd u-showTab">.<br></span>
                <span class="u-hiddenMd u-showTab">&</span>
                Learn other steps in the world for you.
            </h3>
            <button class="c-button p-hero__button">
                <RouterLink class="p-hero__link" to="/register">
                            STEPを始める
                </RouterLink>
            </button>
        </section>

        <!--ヒーローイメージの下側部分-->
        <div id="l-main--siteWidth">
            <!--about部分-->
            <section class="p-about" id="about">
                <h2 class="c-ornament p-about__title c-jsFadeIn">
                    <span class="c-ornament__border">About STEP</span>
                </h2>
                <p class="p-about__para">
                    <span class="c-jsFadeIn">
                      あなたは、いつもどのように<span class="u-showMd"><br></span>新しいことを学んでいますか？<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        例えば、読書・動画学習・習い事・インターネットで調べる・友人に教えてもらうなど、<br>
                        様々な方法で、あなたは新しいことを<span class="u-showMd"><br></span>学んでいませんか？<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        私たちは、いつも様々な方法で<span class="u-showMd"><br></span>新しいことを学んでいます。<br>
                        そして、その学びは実は、<span class="u-showMd"><br></span>既に学んでいる誰かの経験を元にしています。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        例えばピアノ教室。<br>ピアノ教室では、<span class="u-showMd"><br></span>既にピアノを学んでいる先生から、<br>
                        その先生の経験をもとに、<span class="u-showMd"><br></span>あなたは学んでいます。<br>時間とお金を投資して。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        そして、人が学んだ経験を振り返るとき、<br>人それぞれの<span class="u-showMd"><br></span>
                        「これが良かった」という「順番」と<span class="u-showMd"><br></span>
                        「内容」があります。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        あなたにもありませんか？<br>
                        この順番と内容で学ぶと<span class="u-showMd"><br></span>効率的に学べたという経験が。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        STEPは、そんな「この順番でこういったものを<span class="u-showMd u-showTab"><br></span>
                        学んでいったのが良かった」という学びの<span class="u-showMd"><br></span>「STEP」を
                        投稿し、<br>他の人はその「STEP」を見ながら<span class="u-showMd"><br></span>
                        その「STEP」を元に学習を<span class="u-showMd"><br></span>進めていくことができます。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        逆にあなたも、他の人が投稿した「STEP」を<span class="u-showMd"><br></span>見ながら学習を進めていくことができます。<br>
                        あなたのこれまでの「STEP」が<span class="u-showMd"><br></span>
                        誰かの学習のための「STEP」となり、<span class="u-showMd u-showTab"><br></span>
                        あなたにとっても他の人の「STEP」が<br>
                        あなたの学習をより良くする<span class="u-showMd"><br></span>
                        「STEP」になります。<br>
                        <br>
                    </span>
                    <span class="c-jsFadeIn">
                        あなたがこれからも学んでいき、<span class="u-showMd u-showTab"><br></span>
                        あなたの人生をより良くするための<span class="u-showMd"><br></span>一助となるのが「STEP」なのです。<br>
                    </span>
                </p>
            </section>
            <!--STEPの特徴部分-->
            <section class="p-feature c-jsFadeIn">
                <h2 class="c-ornament p-feature__title">
                    <span class="c-ornament__border">STEPの特徴</span>
                </h2>
                <div class="p-feature__flex">
                    <div class="p-feature__item">
                        <div class="p-feature__image">
                            <i class="fa-solid fa-share-nodes p-feature__icon"></i>
                        </div>
                        <h3 class="p-feature__headline">
                            あなただけの「STEP」を共有
                        </h3>
                        <p class="p-feature__para">
                            あなた自身の経験をもとに、「STEP」を作成し共有することができます。「STEP」は
                            内容の概要やサブSTEPを登録できます。あなた自身の経験を他の人に共有し、見てもらいましょう。
                        </p>
                    </div>
                    <div class="p-feature__item">
                        <div class="p-feature__image">
                            <i class="fa-solid fa-check p-feature__icon"></i>
                        </div>
                        <h3 class="p-feature__headline">
                            他の人が投稿した「STEP」に挑戦
                        </h3>
                        <p class="p-feature__para">
                           他の人が投稿した「STEP」に挑戦することができます。挑戦中は、サブSTEPを
                           一つずつクリアしていき、「STEP」を学習していきましょう。
                        </p>
                    </div>
                    <div class="p-feature__item">
                        <div class="p-feature__image">
                            <i class="fa-solid fa-hourglass-half p-feature__icon"></i>
                        </div>
                        <h3 class="p-feature__headline">
                            Mypageで進捗・学習時間を確認
                        </h3>
                        <p class="p-feature__para">
                            Mypageでは、あなたが挑戦している「STEP」の進捗状況や学習時間を確認することができます。
                            たくさんの「STEP」に挑戦し、学習時間を増やしていきましょう。
                        </p>
                    </div>
                </div>
            </section>
            <!--STEPのIndex部分-->
            <section class="p-index c-jsFadeIn">
                <h2 class="c-ornament p-index__title">
                    <span class="c-ornament__border">Index STEP</span>
                </h2>
                <!--STEP表示パネル部分-->
                <div class="c-grid" v-if="indexSteps">
                    <div class="c-panel" v-for="step  in indexSteps" :key="step.id">
                        <RouterLink :to="`/steps/${step.id}`" class="c-panel__routerLink">
                            <!-- アイキャッチ画像 -->
                            <img :src="step.image_path" :alt="step.title + ':アイキャッチ画像'" class="c-panel__image">
                            <p class="c-panel__category">
                              {{ step.category_main }} | {{ step.category_sub }}
                            </p>
                            <h3 class="c-panel__title">
                                {{ step.title }}
                            </h3>
                            <p class="c-panel__para">
                                STEP数:{{step.step_number}}STEP
                            </p>
                            <p class="c-panel__para">
                                挑戦済:{{ step.count_challenger }}人
                            </p>
                            <p class="c-panel__footer">
                                {{ step.created_at }}
                            </p>
                        </RouterLink>
                    </div>
                </div>
                <!--STEP一覧ページへのリンク-->
                <div class="p-index__link">
                    <RouterLink to="/index" class="p-index__url">全てのSTEPを表示する</RouterLink>
                </div>
            </section>
        </div>

    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                indexSteps: null,
                pageNum: 1,
            }
        },
        methods: {
            //登録されているSTEPを取得する
            async fetchAllSteps() {
                await this.$store.dispatch('step/index', this.pageNum)
                //取得したSTEPのうち、先頭から4つのみを取得する
                this.indexSteps = this.$store.getters['step/index'].slice(0, 4)
            },
        },
        created() {
            //create時にfetchAllStepsを実行
            this.fetchAllSteps()
        },
        mounted() {
            //mount時にhashを取得し、#が含まれていたらそのhashまでスクロールさせる処理
            //結果、ヘッダー・フッターのAboutをクリックすると、about部分までクロールする
            const hash = this.$route.hash
            if (hash && hash.match(/^#.+$/)) {
                this.$scrollTo(hash)
            }
        },
        watch: {
            //$routeの監視
            $route: {
                async handler () {
                    this.storeUrlData()
                },
                immediate: true
            },
        }
    }

</script>
