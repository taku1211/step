<template>
    <div id="l-main--siteWidth">
        <!--Myチャレンジ一覧表示画面-->
        <div class="c-allList">
            <div class="c-allList__returnArea">
                <RouterLink to="/mypage" class="c-allList__link">マイページへ戻る</RouterLink>
            </div>
            <h2 class="c-ornament">
                <span class="c-ornament__border">
                    My Challenge 一覧
                </span>
            </h2>

            <!--検索フォーム-->
            <form class="c-allList__searchArea" @submit.prevent="searchSteps">
                <!--メインカテゴリー-->
                <select name="categoryMain" id="categoryMain" class="c-select" @change="changeSubCategory(searchForm.selectedCategoryMain)"
                        v-model="searchForm.selectedCategoryMain">
                    <option :value="categoryMainSearch" class="c-select__option" disabled>
                           メインカテゴリー
                    </option>
                    <option :value="categoryNoSelect" class="c-select__option">
                           選択なし
                    </option>
                    <option :value="category.id" class="c-select__option" v-for="category in categoryList"
                            :key="category.id">{{ category.name }}
                    </option>
                </select>
                <!--サブカテゴリー-->
                <select name="categorySub" id="categorySub" class="c-select"
                    v-model="searchForm.selectedCategorySub">
                    <option :value="categorySubSearch" class="c-select__option" disabled>
                           サブカテゴリー
                    </option>
                    <option :value="categoryNoSelect" class="c-select__option">
                           選択なし
                    </option>
                    <option :value="category.id" class="c-select__option" v-for="category in categoryListSubSelected"
                             :key="category.id">{{ category.name }}
                    </option>
                </select>
                <!--キーワード入力-->
                <input type="text" name="search" id="search" class="c-input c-input--sizeM"
                       placeholder="タイトルをキーワードで検索" v-model="searchForm.keyword">
                <!--検索ボタン-->
                <button class="c-button c-button--orange c-button--sizeM">
                    検索
                </button>
            </form>

            <!--並び替え設定部分-->
            <div class="c-allList__sort">
                <label for="sortBy" class="c-label c-label--rowType">
                    並び替え設定
                </label>
                <select name="sortBy" id="sortBy" class="c-select c-select--sortType" v-model="searchForm.sort" @change="searchSteps">
                    <option value="normal">標準</option>
                    <option value="new">登録が新しい順</option>
                    <option value="old">登録が古い順</option>
                    <option value="firstName">タイトル昇順</option>
                    <option value="lastName">タイトル降順</option>
                </select>
            </div>

            <!--チャレンジしたSTEPを表示する部分-->
            <!--チャレンジしたSTEPが存在する場合-->
            <div class="c-grid" v-if="indexSteps !== null && Array(indexSteps) && indexSteps.length !== 0">
                <div class="c-panel" v-for="step,idx  in indexSteps" :key="idx">
                    <RouterLink :to="`/steps/${step.id}`" class="c-panel__routerLink">
                        <!-- アイキャッチ画像 -->
                        <img :src="step.image_path" :alt="step.title + ':アイキャッチ画像'" class="c-panel__image">
                        <!--カテゴリー表示-->
                        <p class="c-panel__category">
                            {{ step.category_main }} | {{ step.category_sub }}
                        </p>
                        <!--タイトル表示-->
                        <h3 class="c-panel__title">
                            {{ step.title }}
                        </h3>
                        <!--取り組んだ時間表示-->
                        <p class="c-panel__para">
                            取り組んだ時間:
                            {{ (Math.floor(step.stepSumTime/60) !== 0)?Math.floor(step.stepSumTime/60)+'時間':'' }}
                            {{ (step.stepSumTime%60 !== 0)?(step.stepSumTime%60)+'分':'0分' }}
                        </p>
                        <!--進捗表示-->
                        <p class="c-panel__para">
                            達成進捗:
                            {{ step.stepProgress.length }}/{{step.step_number}} STEPクリア
                        </p>
                        <!--進捗バーの表示-->
                        <div class="c-panel__barArea">
                            <progress  class="c-panel__bar" :value="(step.stepProgress.length)/(step.step_number)*100" max="100">
                            </progress>
                            <p class="c-panel__progress">
                                {{ Math.floor((step.stepProgress.length)/(step.step_number)*100)}}%
                            </p>
                        </div>
                    </RouterLink>
                </div>
            </div>
            <!--チャレンジしたSTEPがない場合-->
            <div class="c-allList__para" v-else-if="indexSteps !== null && Array(indexSteps) && indexSteps.length === 0 && !searchFlg">
                STEPはまだ登録されていません。
            </div>
            <!--検索条件に該当する、チャレンジしたSTEPがない場合-->
            <div class="c-allList__para" v-else-if="indexSteps !== null && Array(indexSteps) && indexSteps.length === 0 && searchFlg">
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
    import CategoryListJson from "./../../json/categoryList.json"

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
                categoryList:CategoryListJson["mainCategory"],
                categoryListSubSelected:[],
                categoryMainSearch: 'メインカテゴリーを選択してください',
                categorySubSearch: 'サブカテゴリーを選択してください',
                categoryNoSelect: '選択なし',
            }
        },
        methods: {
            //登録されている挑戦中のSTEPを取得する
            async fetchAllMyChallenge() {
                await this.$store.dispatch('step/indexMyChallenge', this.page)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                this.getPageData()
            },
            //検索ボタンを押したときの検索処理
            async searchSteps(){
                this.prepareBeforeSearch()

                await this.$store.dispatch('step/searchMyChallenge', this.searchForm)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                this.getPageData()
            },
            //検索された状態でのページネーション処理
            async fetchSearchSteps(){
                this.prepareSearchedPagination()

                await this.$store.dispatch('step/searchMyChallenge', this.searchForm)
                this.indexSteps = this.$store.getters['step/myChallenge']

                this.getMyChallengeInfo()

                this.getPageData()
            },
            //チャレンジ中のSTEPの自分の挑戦データ・進捗数・取り組み時間を取得
            getMyChallengeInfo(){

                if(this.indexSteps && Array(this.indexSteps)){
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
                }
            },
        },
        watch: {
            //$routeの監視
            $route: {
                //$routeの変更感知時、
                async handler () {
                    //vuexに保持した検索条件がデフォルトであれば
                    if(this.searchForm.selectedCategoryMain === this.categoryMainSearch
                        && this.searchForm.keyword === ''
                        && this.searchForm.selectedCategorySub === this.categorySubSearch
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

                    //URLの「?page=」以降に直接、不正な値が入力された場合の対策
                    this.checkInvalidPageNum()

                    //Vuexに表示ページのURL情報を保存する
                    this.storeUrlData()
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
