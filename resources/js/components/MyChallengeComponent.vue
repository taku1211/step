<template>
    <div id="l-siteWidth">
        <!--Myチャレンジ一覧表示画面-->
        <div class="p-allMyChallenge">
            <div class="p-allMyChallenge__returnLink">
                <RouterLink to="/mypage" class="p-allMyChallenge__url">マイページへ戻る</RouterLink>
            </div>
            <h2 class="c-ornament p-allMyChallenge__title">
                <span class="c-ornament__border p-allMyChallenge__border">
                    My Challenge 一覧
                </span>
            </h2>

            <!--検索フォーム-->
            <form class="c-form p-allMyChallenge__form" @submit.prevent="searchSteps">
                <!--メインカテゴリー-->
                <select name="categoryMain" id="categoryMain" class="c-input p-allMyChallenge__select" @change="changeSubCategory(searchForm.selectedCategoryMain)"
                        v-model="searchForm.selectedCategoryMain">
                    <option value="メインカテゴリーを選択してください" class="p-allMyChallenge__option" disabled>
                           メインカテゴリー
                    </option>
                    <option value="メインカテゴリーを選択してください" class="p-allMyChallenge__option">
                           選択なし
                    </option>
                    <option :value="category" class="p-allMyChallenge__option" v-for="category in categoryListMain"
                            :key="category">{{ category }}
                    </option>
                </select>
                <!--サブカテゴリー-->
                <select name="categorySub" id="categorySub" class="c-input p-allMyChallenge__select"
                    v-model="searchForm.selectedCategorySub">
                    <option value="サブカテゴリーを選択してください" class="p-allMyChallenge__option" disabled>
                           サブカテゴリー
                    </option>
                    <option value="サブカテゴリーを選択してください" class="p-allMyChallenge__option">
                           選択なし
                    </option>
                    <option :value="category" class="p-allMyChallenge__option" v-for="category in categoryListSubSelected"
                             :key="category">{{ category }}
                    </option>
                </select>
                <!--キーワード入力-->
                <input type="text" name="search" id="search" class="c-input p-allMyChallenge__input"
                       placeholder="タイトルをキーワードで検索" v-model="searchForm.keyword">
                <!--検索ボタン-->
                <button class="c-button p-allMyChallenge__button">
                    検索
                </button>
            </form>

            <!--並び替え設定部分-->
            <div class="p-allMyChallenge__sort">
                <label for="sortBy" class="c-label p-allMyChallenge__label">
                    並び替え設定
                </label>
                <select name="sortBy" id="sortBy" class="c-input p-allMyChallenge__selectSort" v-model="searchForm.sort" @change="searchSteps">
                    <option value="normal">標準</option>
                    <option value="new">登録が新しい順</option>
                    <option value="old">登録が古い順</option>
                    <option value="firstName">タイトル昇順</option>
                    <option value="lastName">タイトル降順</option>
                </select>
            </div>

            <!--チャレンジしたSTEPを表示する部分-->
            <!--チャレンジしたSTEPが存在する場合-->
            <div class="c-grid p-allMyChallenge__grid" v-if="indexSteps !== null && indexSteps.length !== 0">
                <div class="c-panel p-allMyChallenge__panel" v-for="step,idx  in indexSteps" :key="idx">
                    <RouterLink :to="`/steps/${step.id}`" class="c-panel__routerLink p-allMyChallenge__routerLink">
                        <!--カテゴリー表示-->
                        <p class="c-panel__category p-allMyChallenge__category">
                            {{ step.category_main }} | {{ step.category_sub }}
                        </p>
                        <!--タイトル表示-->
                        <h3 class="c-panel__title p-allMyChallenge__stepTitle">
                            {{ step.title }}
                        </h3>
                        <!--目安達成時間表示-->
                        <p class="c-panel__para p-allMyChallenge__stepPara">
                            目安達成時間:
                            {{ (Math.floor(step.time_aim/60) !== 0)?Math.floor(step.time_aim/60)+'時間':'' }}
                            {{ (step.time_aim%60 !== 0)?(step.time_aim%60)+'分':'' }}
                        </p>
                        <!--取り組んだ時間表示-->
                        <p class="c-panel__para p-allMyChallenge__stepPara">
                            取り組んだ時間:
                            {{ (Math.floor(step.stepSumTime/60) !== 0)?Math.floor(step.stepSumTime/60)+'時間':'' }}
                            {{ (step.stepSumTime%60 !== 0)?(step.stepSumTime%60)+'分':'0分' }}
                        </p>
                        <!--進捗表示-->
                        <p class="c-panel__para p-allMyChallenge__stepPara">
                            達成進捗:
                            {{ step.stepProgress.length }}/{{step.step_number}} STEPクリア
                        </p>
                        <!--進捗バーの表示-->
                        <div class="c-panel__barArea p-allMyChallenge__barArea">
                            <progress  class="c-panel__bar p-allMyChallenge__bar" :value="(step.stepProgress.length)/(step.step_number)*100" max="100">
                            </progress>
                            <p class="c-panel__progress p-allMyChallenge__progress">
                                {{ Math.floor((step.stepProgress.length)/(step.step_number)*100)}}%
                            </p>
                        </div>
                        <!--STEP自体が登録された日付表示-->
                        <p class="c-panel__footer p-allMyChallenge__footer">  {{ step.created_at }}</p>
                    </RouterLink>
                </div>
            </div>
            <!--チャレンジしたSTEPがない場合-->
            <div class="p-allMyChallenge__para" v-else-if="indexSteps !== null && indexSteps.length === 0 && !searchFlg">
                STEPはまだ登録されていません。
            </div>
            <!--検索条件に該当する、チャレンジしたSTEPがない場合-->
            <div class="p-allMyChallenge__para" v-else-if="indexSteps !== null && indexSteps.length === 0 && searchFlg">
                検索条件のSTEPはまだ登録されていません。
            </div>
            <!--ページネーション-->
            <PaginationComponent pageType="mypage/challenge" :current-page="currentPage" :last-page="lastPage"></PaginationComponent>
        </div>
    </div>
</template>

<script>
    import PaginationComponent from './PaginationComponent.vue'
    import store from './../store/index'

    export default {
        components: {
            PaginationComponent,
        },
        props: {
            page: {
                type: Number,
                required: false,
                default: 1
            }
        },
        data: function() {
            return {
                searchForm:{
                    keyword: this.$store.getters['step/myChallengeSearchKeyword'],
                    selectedCategoryMain:this.$store.getters['step/myChallengeSearchCategoryMain'],
                    selectedCategorySub:this.$store.getters['step/myChallengeSearchCategorySub'],
                    page:1,
                    sort:'normal',
                },
                searchFlg:false,
                indexSteps: null,
                currentPage: 1,
                lastPage: 0,
                userId: this.$store.getters['auth/id'],
                categoryListMain:['自己啓発','ビジネススキル','開発','デザイン','財務会計','ITとソフトウェア','マーケティング',
                                  '趣味・実用・ホビー','写真と動画','健康・フィットネス','音楽','教育・教養'],
                categoryListSubSelected:[],
                categoryListSub1:['目標達成','生産性向上','リーダーシップ','キャリア','子育て&家族','ポジティブシンキング','哲学・宗教','パーソナルブランディング',
                                  'クリエイティブスキル','コミュニケーションスキル','ストレス管理','記憶力向上','モチベーション','その他の自己啓発'],
                categoryListSub2:['新規事業開発','コミュニケーション','チームマネジメント','営業・販売スキル','ビジネス戦略','業務オペレーション',
                                  '法務知識','プロジェクト管理','ビジネスアナリティクス','人事','業界別スキル','Eコマース','メディア活用','不動産投資','その他のビジネス'],
                categoryListSub3:['ウェブ開発','データサイエンス','モバイル開発','プログラミング言語','ゲーム開発','DBデザイン・開発','ソフトウェアテスト',
                                    'ソフトウェアエンジニアリング','ソフトウェア開発ツール','コードなしの開発','その他の開発'],
                categoryListSub4:['ウェブデザイン','グラフィックデザインとイラストレーション','デザインツール','UX（ユーザー体験）デザイン','ゲームデザイン',
                                  '3D・アニメーション','ファッションデザイン','建築デザイン','インテリアデザイン','その他のデザイン'],
                categoryListSub5:['会計＆簿記','コンプライアンス','暗号通貨＆ブロックチェーン','経済学','ファイナンス','ファイナンス資格','財務モデリング・分析',
                                  '投資・株式','資金管理','税金','その他の財務会計'],
                categoryListSub6:['IT資格','ネットワークとセキュリティ','ハードウェア','OSとソフトウェア','その他のIT・ソフトウェア'],
                categoryListSub7:['デジタルマーケティング','SEO','SNSマーケティング','ブランディング','マーケティングの基礎','市場分析と自動化','PR','動画・モバイルマーケティング',
                                  'コンテンツマーケティング','アフィリエイトマーケティング','プロダクトマーケティング','その他のマーケティング'],
                categoryListSub8:['アート・ものづくり','ビューティー','エソテリックプラクティス','料理','ゲーム','DIY・リフォーム','ガーデニング','アウトドア',
                                  'ペット','旅行','その他の趣味・実用・ホビー'],
                categoryListSub9:['デジタル写真','写真','人物写真撮影','撮影ツール','映像制作','その他の写真と動画'],
                categoryListSub10:['エクササイズ','健康','スポーツ','栄養学＆ダイエット','ヨガ','心のケア','武道＆護身術','応急措置','ダンス','瞑想','その他の健康・フィットネス'],
                categoryListSub11:['楽器演奏','作詞・作曲','音楽の基礎','ボイストレーニング','演奏テクニック','音楽ソフトの使い方','その他の音楽'],
                categoryListSub12:['エンジニアリング','人文科学','数学','科学','オンライン教育','社会学','言語','講師向けトレーニング','入試・資格','その他の教育・教養'],
            }
        },
        methods: {
            //登録されている挑戦中のSTEPを取得する
            async fetchAllMyChallenge() {
                await this.$store.dispatch('step/indexMyChallenge', this.page)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                //ページネーションのための現在のページ数・最終ページを取得
                this.currentPage = this.$store.getters['step/currentPage']
                this.lastPage = this.$store.getters['step/lastPage']
            },
            //検索ボタンを押したときの検索処理
            async searchSteps(){
                //検索ボタンを押して検索した場合は、必ず1ページ目を表示するようにする
                this.searchForm.page = 1
                this.searchFlg = true

                if(this.searchForm.selectedCategoryMain === 'メインカテゴリーを選択してください'){
                    this.searchForm.selectedCategoryMain = ''
                }

                if(this.searchForm.selectedCategorySub === 'サブカテゴリーを選択してください'){
                    this.searchForm.selectedCategorySub = ''
                }

                await this.$store.dispatch('step/searchMyChallenge', this.searchForm)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                //ページネーションのための現在のページ数・最終ページを取得
                this.currentPage = this.$store.getters['step/currentPage']
                this.lastPage = this.$store.getters['step/lastPage']
            },
            //検索された状態でのページネーション処理
            async fetchSearchSteps(){
                //検索ボタンを押して検索した場合と異なり、
                //searchForm.pageを1にせず、2ページ目以降に進めるようにする

                if(this.searchForm.selectedCategoryMain === 'メインカテゴリーを選択してください'){
                    this.searchForm.selectedCategoryMain = ''
                }

                if(this.searchForm.selectedCategorySub === 'サブカテゴリーを選択してください'){
                    this.searchForm.selectedCategorySub = ''
                }

                await this.$store.dispatch('step/searchMyChallenge', this.searchForm)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                this.currentPage = this.$store.getters['step/currentPage']
                this.lastPage = this.$store.getters['step/lastPage']
            },
            //チャレンジ中のSTEPの自分の挑戦データ・進捗数・取り組み時間を取得
            getMyChallengeInfo(){

                //チャレンジ中の自分の挑戦データを取得する
                for(let i=0;i<this.indexSteps.length;i++){

                    //dataのuserIdをエスケープ
                    let userId = this.userId

                    //コントローラー側で取得したchallenge_stepには、他ユーザーの挑戦データも含まれているため、
                    //userIdでfilterし、challenge_stepを自分の挑戦データのみに更新する
                    const myChallenge = this.indexSteps[i]['challenge_step'].filter(function(object){
                        if(object['user_id'] === userId){
                            return true
                        }
                    })
                    this.indexSteps[i]['challenge_step'] = myChallenge
                }

                //サブSTEPの進捗状況を計算
                for(let i=0;i<this.indexSteps.length;i++){
                    const cleardStep = this.indexSteps[i]['challenge_step'].filter(function(object){
                        //challenge_stepには、そのSTEP自体に登録した挑戦データ(substep_idがnull)
                        //＋各サブSTEP用の挑戦データ(substep_idがnullではい)の2種類が存在する
                        //そのため、各サブSTEP用の挑戦データであり、かつクリア済(clear_flgがtrue)のものを
                        //filterすると、サブSTEPの進捗状況を計算できる
                        if(object['clear_flg'] === 1 && object['substep_id'] !== null){
                            return true
                        }
                    })
                    this.indexSteps[i]['stepProgress'] = cleardStep
                }
                //挑戦中のSTEPの総取り組み時間を取得
                for(let i=0;i<this.indexSteps.length;i++){
                    this.indexSteps[i]['challenge_step'].forEach(object=>{
                        //そのSTEP自体に登録した挑戦データ(substep_idがnull)に登録されているtimeは
                        //各サブSTEPの取り組み時間を合計した数値が登録されている
                        //そのため上記timeを取得する
                        if(object['substep_id'] === null) {
                            this.indexSteps[i]['stepSumTime'] = object['time']
                        }
                    })
                }
            },
            //サブカテゴリーの表示項目を変更する
            changeSubCategory(category){
                this.searchForm.selectedCategorySub = 'サブカテゴリーを選択してください'

                switch(category){//引数のカテゴリーに連動して、サブカテゴリーの項目を変更する
                    case '自己啓発':
                        this.categoryListSubSelected = this.categoryListSub1
                        break;
                    case 'ビジネススキル':
                        this.categoryListSubSelected = this.categoryListSub2
                        break;
                    case '開発':
                        this.categoryListSubSelected = this.categoryListSub3
                        break;
                        case 'デザイン':
                        this.categoryListSubSelected = this.categoryListSub4
                        break;
                    case '財務会計':
                        this.categoryListSubSelected = this.categoryListSub5
                        break;
                    case 'ITとソフトウェア':
                        this.categoryListSubSelected = this.categoryListSub6
                        break;
                    case 'マーケティング':
                        this.categoryListSubSelected = this.categoryListSub7
                        break;
                    case '趣味・実用・ホビー':
                        this.categoryListSubSelected = this.categoryListSub8
                        break;
                    case '写真と動画':
                        this.categoryListSubSelected = this.categoryListSub9
                        break;
                    case '健康・フィットネス':
                        this.categoryListSubSelected = this.categoryListSub10
                        break;
                    case '音楽':
                        this.categoryListSubSelected = this.categoryListSub11
                        break;
                    case '教育・教養':
                        this.categoryListSubSelected = this.categoryListSub12
                        break;
                    default:
                        this.categoryListSubSelected = []
                }
            },
        },
        watch: {
            //$routeの監視
            $route: {
                //$routeの変更感知時、
                async handler () {
                    //vuexに保持した検索条件がデフォルトであれば
                    if(this.searchForm.selectedCategoryMain === 'メインカテゴリーを選択してください'
                        && this.searchForm.keyword === ''
                        && this.searchForm.selectedCategorySub === 'サブカテゴリーを選択してください'
                        && this.searchForm.sort === 'normal'){
                        //何も処理しない
                    }else{
                        //$routeの変更時、pagination以外の変更ではsearchFlgがfalseに戻るため、
                        //デフォルトでなければsearchFlgをtrueにし、続く処理で検索処理を行う
                        this.searchFlg = true
                    }
                    //検索状態を確認する前にpaginationの何page目なのかを取得
                    this.searchForm.page = this.page

                    //検索状態でなければ
                    if(this.searchFlg === false){
                        //fetchAllMyChallengeを実行し、挑戦したチャレンジを取得
                        await this.fetchAllMyChallenge()

                    //検索している状態であれば
                    }else{
                        //fetchSearchStepsを実行し、検索条件の挑戦したチャレンジを取得
                        await this.fetchSearchSteps()
                    }

                    //以下は、URLの「?page=」以降に直接、不正な値が入力された場合の対策

                    //表示ページのsearch情報を取得
                    const search = location.search
                    //routingで管理しているページ情報を取得
                    const page = '?page=' + this.page

                    if(search !== '' && search !== page  ){
                        //一致しない場合（不正な値など）は、404notFoundエラーページへ遷移
                        this.$router.push('*')
                    }

                    //Vuexに表示ページのURL情報を保存する
                    //この処理を行うことで、STEP詳細からこのページへ戻る際に、戻るページのpathを特定している
                    this.$store.dispatch('route/setLocationUrl',(location.pathname+location.search))
                    this.$store.dispatch('route/setLocationPath',(location.pathname))
                },
                immediate: true
            },
        },
        //他ページからの遷移前のアクション
        beforeRouteEnter(to, from, next){
            //STEP詳細ページからの遷移の場合
            if(from.path.includes('/steps/')){
                //何も処理しない
                //保存している検索条件を保持し、その検索条件に合わせたページを再度表示させる
            }else{
                //それ以外のページからの遷移の場合、vuexで管理している検索条件をリセット
                store.dispatch('step/resetMyChallengeSearchWord')
            }
            next()
        }
    }

</script>
