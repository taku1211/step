<template>
    <div id="l-main--siteWidth">
        <!--My page表示画面-->
        <div class="p-mypage">
            <!--ユーザー情報表示部分-->
            <div class="p-mypage__userInfo">
                <img :src="myIcon" alt="プロフィール画像" class="p-mypage__userIcon">
                <p class="p-mypage__userMail">ようこそ {{ myEmail }}さん</p>
            </div>

            <!--自分が登録したSTEPの表示部分-->
            <h2 class="c-ornament">
                <span class="c-ornament__border">
                    My STEP
                </span>
            </h2>
                <div class="p-mypage__content">
                    <!--サブSTEPが登録されていないSTEPがある場合、警告メッセージを表示-->
                    <div class="c-message p-mypage__message" v-if="mustRegisterSubStepFlg">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        サブSTEPが登録されていないSTEPがあります。サブSTEPを登録して他ユーザーに挑戦してもらいましょう！
                    </div>
                    <!--STEPの登録ボタン-->
                    <div class="c-submit p-mypage__submit">
                        <button class="c-button p-mypage__button">
                            <RouterLink class="p-mypage__link" to="/new">
                                STEPを登録
                            </RouterLink>
                        </button>
                    </div>
                    <!--自分が登録したSTEPを一つずつ表示する部分-->
                    <!--自分が登録したSTEPが存在する場合-->
                    <div class="c-grid" v-if="mySteps !== null && Array(mySteps) && mySteps.length !== 0">
                        <div class="c-panel p-mypage__panel" v-for="step  in mySteps" :key="step.id">
                            <RouterLink :to="`/steps/${step.id}`" class="c-panel__routerLink p-mypage__routerLink">
                                <!-- アイキャッチ画像 -->
                                <img :src="step.image_path" :alt="step.title + ':アイキャッチ画像'" class="c-panel__image">

                                <div class="c-panel__head p-mypage__head">
                                    <span class="c-budge p-mypage__budge" :class="(step.step_number === 0)? 'c-budge--warning':''">
                                        {{ (step.step_number === 0)? '非公開':'公開済' }}
                                    </span>
                                </div>
                                <!--カテゴリー表示-->
                                <p class="c-panel__category p-mypage__category">
                                    {{ step.category_main }} | {{ step.category_sub }}
                                </p>
                                <!--タイトル表示-->
                                <h3 class="c-panel__title p-mypage__stepTitle">
                                    {{ step.title }}
                                </h3>
                                <!--サブSTEP数の表示-->
                                <!-- <p class="c-panel__para p-mypage__stepPara">STEP数:{{step.step_number}}STEP</p> -->
                                <!--挑戦中人数の表示-->
                                <!-- <p class="c-panel__para p-mypage__stepPara">挑戦中:{{ step.count_challenger }}人</p> -->
                                <!--STEP編集ボタンの表示-->
                                <div class="c-panel__footer c-panel__footer--flex p-mypage__footer">
                                    <button class="c-button p-mypage__editButton">
                                        <RouterLink class="p-mypage__editLink" :to="`/edit/${step.id}`">
                                            編集<span class="u-hiddenMd">する</span>
                                        </RouterLink>
                                    </button>
                                </div>
                            </RouterLink>
                        </div>
                    </div>
                    <!--自分が登録したSTEPがない場合-->
                    <div class="p-mypage__para" v-else-if="mySteps !== null && Array(mySteps) && mySteps.length === 0">
                        登録したSTEPはありません。
                        「STEPを登録」ボタンからSTEPを登録してみましょう。
                    </div>
                    <!--自分が登録したSTEPの一覧表示ページへのリンク-->
                    <div class="p-mypage__toAll">
                        <RouterLink to="/mypage/index" class="p-mypage__url">全てのSTEPを表示する</RouterLink>
                    </div>
                </div>

                <!--Myチャレンジ表示部分-->
                <h2 class="c-ornament">
                    <span class="c-ornament__border">
                        My Challenge
                    </span>
                </h2>

                <!--チャレンジしたSTEPをひとつずつ表示する部分-->
                <!--チャレンジしたSTEPが存在する場合-->
                <div class="p-mypage__content">
                    <div class="c-grid p-mypage__grid" v-if="myChallenge !== null && Array(myChallenge) && myChallenge.length !== 0">
                        <div class="c-panel p-mypage__panel" v-for="step, idx  in myChallenge" :key="idx">
                            <RouterLink :to="`/steps/${step.id}`" class="c-panel__routerLink p-mypage__routerLink">
                                <!-- アイキャッチ画像 -->
                                <img :src="step.image_path" :alt="step.title + ':アイキャッチ画像'" class="c-panel__image">

                                <!--カテゴリー表示-->
                                <p class="c-panel__category p-mypage__category">
                                    {{ step.category_main }} | {{ step.category_sub }}
                                </p>
                                <!--タイトル表示-->
                                <h3 class="c-panel__title p-mypage__stepTitle">
                                    {{ step.title }}
                                </h3>
                                 <!--取り組んだ時間表示-->
                                <p class="c-panel__para p-mypage__stepPara">
                                    取り組んだ時間:
                                    {{ (Math.floor(step.stepSumTime/60) !== 0)?Math.floor(step.stepSumTime/60)+'時間':'' }}
                                    {{ (step.stepSumTime%60 !== 0)?(step.stepSumTime%60)+'分':'0分' }}
                                </p>
                                <!--進捗表示-->
                                <p class="c-panel__para p-mypage__stepPara">
                                    達成進捗:{{ step.stepProgress.length }}/{{step.step_number}} STEPクリア
                                </p>
                                <!--進捗バーの表示-->
                                <div class="c-panel__barArea p-mypage__barArea">
                                    <progress  class="c-panel__bar p-myapge__bar" :value="(step.stepProgress.length)/(step.step_number)*100" max="100">
                                    </progress>
                                    <p class="c-panel__progress p-mypage__progress">
                                        {{Math.floor((step.stepProgress.length)/(step.step_number)*100)}}%
                                    </p>
                                </div>
                            </RouterLink>
                        </div>
                    </div>
                    <!--チャレンジしたSTEPがない場合-->
                    <div class="p-mypage__para" v-else-if="myChallenge !== null && Array(myChallenge) && myChallenge.length === 0">
                        挑戦したSTEPはありません。
                        <RouterLink class="p-mypage__route" to="/index">
                            STEP一覧
                        </RouterLink>
                        で他のユーザーのSTEPを見てみましょう。
                    </div>
                    <!--自分がチャレンジしたSTEPの一覧表示ページへのリンク-->
                    <div class="p-mypage__toAll">
                        <RouterLink to="/mypage/challenge" class="p-mypage__url">全てのChallengeを表示する</RouterLink>
                    </div>
                </div>
        </div>
    </div>
</template>

<script>
    export default {
        data:function(){
            return{
                myEmail:this.$store.getters['auth/email'],
                myIcon: (this.$store.getters['auth/icon']) ? '/storage/'+this.$store.getters['auth/icon']:'/images/icon_user.svg',
                mySteps: [],
                myChallenge: [],
                page: 1,
                mustRegisterSubStepFlg:false,
                userId: this.$store.getters['auth/id'],
            }
        },
        methods: {
            //自分が登録したSTEPを全て取得する
            async getAllMySteps(){
                await this.$store.dispatch('step/indexMySteps', this.page)
                this.mySteps = this.$store.getters['step/mySteps']

                //取得したSTEPの内、step_numberが0（サブSTEPが0）のSTEPがある場合は、
                //mustRegisterSubStepFlgをtrueに変更し、ページにメッセージを表示させる
                if(this.mySteps && Array(this.mySteps)){
                    for(let i=0;i<this.mySteps.length;i++){
                        if(this.mySteps[i]['step_number'] === 0){
                            this.mustRegisterSubStepFlg = true
                        }
                     }
                }

            },
            //自分が挑戦したSTEPを全て取得する
            async getAllMyChallenge(){
                await this.$store.dispatch('step/indexMyChallenge', this.page)
                this.myChallenge = this.$store.getters['step/myChallenge']

                this.getAllMyChallengeInfo()
            },
            //チャレンジ中のSTEPの自分の挑戦データ・進捗数・取り組み時間を取得
            getAllMyChallengeInfo(){
                if(!this.myChallenge || !Array(this.myChallenge)){
                    return false
                }

                //チャレンジ中の自分の挑戦データを取得する
                for(let i=0;i<this.myChallenge.length;i++){

                    //dataのuserIdをエスケープ
                    let userId = this.userId

                    //コントローラー側で取得したchallenge_stepには、他ユーザーの挑戦データも含まれているため、
                    //userIdでfilterを実施し、challenge_stepを自分の挑戦データのみに更新する
                    const myChallenge = this.myChallenge[i]['challenge_step'].filter(function(object){
                        if(object['user_id'] === userId){
                            return true
                        }
                    })
                    this.myChallenge[i]['challenge_step'] = myChallenge
                }

                //サブSTEPの進捗状況を計算
                for(let i=0;i<this.myChallenge.length;i++){
                    const cleardStep = this.myChallenge[i]['challenge_step'].filter(function(object){
                        //challenge_stepには、そのSTEP自体に登録した挑戦データ(substep_idがnull)
                        //＋各サブSTEP用の挑戦データ(substep_idがnullではい)の2種類が存在する
                        //そのため、各サブSTEP用の挑戦データであり、かつクリア済(clear_flgがtrue)のものを
                        //filterすると、サブSTEPの進捗状況を計算できる
                        if(object['clear_flg'] === 1 && object['substep_id'] !== null){
                            return true
                        }
                    })
                    this.myChallenge[i]['stepProgress'] = cleardStep
                }
                //挑戦中のSTEPの総取り組み時間を取得
                for(let i=0;i<this.myChallenge.length;i++){
                    this.myChallenge[i]['challenge_step'].forEach(object=>{
                        //そのSTEP自体に登録した挑戦データ(substep_idがnull)に登録されているtimeは
                        //各サブSTEPの取り組み時間を合計した数値が登録されている
                        //そのため上記timeを取得する
                        if(object['substep_id'] === null) {
                            this.myChallenge[i]['stepSumTime'] = object['time']
                        }
                    })
                }
            },
        },
        watch: {
            //$routeの監視
            $route: {
                //$routeの変更が感知されたら、
                async handler () {

                    //自分が登録したSTEPと、挑戦したSTEPを取得する
                    await this.getAllMySteps()
                    await this.getAllMyChallenge()

                    //URLの「?page=」以降に直接、不正な値が入力された場合の対策
                    this.checkInvalidPageNum()

                    //Vuexに表示ページのURL情報を保存する
                    this.storeUrlData()
                },
                immediate: true
            }
        }
    }

</script>
