<template>
    <div id="l-siteWidth">
        <!--STEP詳細ページ-->
        <div class="p-detail" v-if="step">
                <!--遷移前のページのURLに合わせて、前のページへ戻るリンクを表示-->
                <RouterLink :to="`${prevRoute}`" class="p-detail__link" v-if="prevPath === '/mypage'">マイページへ戻る</RouterLink>
                <RouterLink :to="`${prevRoute}`" class="p-detail__link" v-else-if="prevPath === '/index'">STEP一覧ページへ戻る</RouterLink>
                <RouterLink :to="`${prevRoute}`" class="p-detail__link" v-else-if="prevPath === '/mypage/index'">マイSTEP一覧ページへ戻る</RouterLink>
                <RouterLink :to="`${prevRoute}`" class="p-detail__link" v-else-if="prevPath === '/mypage/challenge'">マイChallenge一覧ページへ戻る</RouterLink>
                <RouterLink :to="`${prevRoute}`" class="p-detail__link" v-else>トップページへ戻る</RouterLink>

            <!--Twitterシェアボタン-->
            <span class="p-detail__share" @click="shareTwitter">
                <i class="fa-brands fa-twitter"></i>
                シェアする
            </span>
            <h2 class="c-ornament p-detail__title">
                <span class="c-ornament__border p-detail__border">
                    {{ step.title }}
                </span>
            </h2>
            <!--STEP詳細の表示-->
            <div class="p-detail__flex">
                <!--STEPの概要表示（ページ左側）-->
                <!--widthが768px以下の場合は、ページ上側-->
                <div class="p-detail__left">
                    <p class="p-detail__para p-detail__para--title">
                        ♢STEPタイトル
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ step.title }}
                    </p>
                    <p class="p-detail__para p-detail__para--title">
                        ♢カテゴリー
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ step.category_main }} | {{ step.category_sub }}
                    </p>
                    <p class="p-detail__para p-detail__para--title">
                        ♢目安達成時間
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ (Math.floor(step.time_aim/60) !== 0)?Math.floor(step.time_aim/60)+'時間':'' }}
                        {{ (step.time_aim%60 !== 0)?(step.time_aim%60)+'分':'' }}
                    </p>
                    <p class="p-detail__para p-detail__para--title">
                        ♢ステップ数
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ step.step_number }}STEP
                    </p>
                    <p class="p-detail__para p-detail__para--title">
                        ♢挑戦済みのユーザー数
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ step.count_challenger }}人
                    </p>
                    <p class="p-detail__para p-detail__para--title">
                        ♢ステップ概要
                    </p>
                    <p class="p-detail__para u-leftMargin__l">
                        {{ (step.content !== null)? step.content : '概要は登録されていません。' }}
                    </p>
                    <!--このSTEPを登録したユーザー情報の表示-->
                    <p class="p-detail__para p-detail__para--title">
                        ♢ユーザー情報
                    </p>
                    <div class="p-detail__userInfo">
                        <div class="p-detail__iconBox">
                            <img class="p-detail__userIcon" :src="userIcon" alt="ユーザーアイコン">
                        </div>
                        <p class="p-detail__userIntroduction">
                            {{ userIntroduction }}
                        </p>
                    </div>
                </div>
                <!--STEPのサブSTEP表示（ページ右側）-->
                <!--widthが768px以下の場合は、ページ下側-->

                <!--登録したユーザーとログインユーザーが異なる、もしくはログイン状態ではない場合に表示-->
                <div class="p-detail__right" v-if="Number(this.step['user_id']) !==  Number(this.userId)">
                    <div class="p-detail__sub" v-for="(subStep, idx) in step.substeps" :key="idx">
                        <p class="p-detail__sub-para p-detail__sub-para--left">
                            STEP{{ idx+1 }}
                        </p>
                        <RouterLink :to="`/substeps/${subStep.id}`" class="p-detail__sub-para p-detail__sub-para--center">
                            <p class="p-detail__subTitle">
                                {{ subStep.title }}
                            </p>
                        </RouterLink>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済ではない、かつ、このサブSTEPに挑戦済の場合-->
                        <p class="p-detail__sub-para p-detail__sub-para--right p-detail__sub-para--orange"
                           v-if="myChallenge.length !== 0 && myChallenge[idx+1]['clear_flg'] === 0 && myChallenge[idx+1]['challenge_flg'] === 1">
                            チャレンジ中
                        </p>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済、かつ、このサブSTEPに挑戦済の場合-->
                        <p class="p-detail__sub-para p-detail__sub-para--right p-detail__sub-para--green"
                           v-else-if="myChallenge.length !== 0 && myChallenge[idx+1]['clear_flg'] === 1 && myChallenge[idx+1]['challenge_flg'] === 1">
                            クリア
                        </p>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済みではない、かつ、このサブSTEPにまだ挑戦していない場合-->
                        <p class="p-detail__sub-para p-detail__sub-para--right" v-else>
                            <i class="fa-solid fa-lock"></i>
                        </p>
                    </div>
                 </div>
                <!--登録したユーザーとログインユーザーが同じ場合-->
                 <div class="p-detail__right" v-else>
                    <div class="p-detail__sub" v-for="(subStep, idx) in step.substeps" :key="idx">
                        <p class="p-detail__sub-para p-detail__sub-para--left">
                            STEP{{ idx+1 }}
                        </p>
                        <RouterLink :to="`/substeps/${subStep.id}`" class="p-detail__sub-para p-detail__sub-para--center">
                            <p class="p-detail__subTitle">
                                {{ subStep.title }}
                            </p>
                        </RouterLink>
                        <!--チャレンジ中・クリアなどを表示するのではなく、各サブSTEPの目安達成時間を表示する-->
                        <p class="p-detail__sub-para p-detail__sub-para--right">
                            {{ (Math.floor(subStep.time_aim/60) !== 0)?Math.floor(subStep.time_aim/60)+'時間':'' }}
                            {{ (subStep.time_aim%60 !== 0)?(subStep.time_aim%60)+'分':'' }}
                        </p>
                    </div>
                 </div>
            </div>

            <!--このSTEPへの挑戦状態を表示するボタン-->
            <!--登録したユーザーとログインユーザーが異なる場合に表示-->
            <div class="p-detail__submit" v-if="this.userId !== null && Number(this.step['user_id']) !==  Number(this.userId)">
                <!--まだこのSTEPに挑戦していない場合に表示-->
                <button class="c-button p-detail__button p-detail__button--orange"
                        v-if="!this.challengeFlg && Number(this.step['step_number']) !==  0"
                        @click="challengeStep(id)">
                        チャレンジ
                </button>
                <!--このSTEPに挑戦済で、かつまだサブSTEPをすべてクリアしていない場合に表示-->
                <button class="c-button p-detail__button p-detail__button--green" disabled
                        v-else-if="this.challengeFlg && !this.clearFlg && Number(this.step['step_number']) !==  0">
                    チャレンジ中
                </button>
                <!--このSTEPに挑戦済で、かつサブSTEPもすべてクリアした場合に表示-->
                <button class="c-button p-detail__button p-detail__button--green" disabled
                        v-else-if="this.challengeFlg && this.clearFlg && Number(this.step['step_number']) !==  0">
                    クリア済
                </button>
                <!--このSTEPにサブSTEPが登録されていない（挑戦できない）場合に表示-->
                <p class="p-detail__alert" v-else>
                    サブSTEPが登録されていないため、このSTEPには挑戦できません。
                </p>
            </div>

            <!--このSTEPの編集リンクへと遷移するためのボタン-->
            <!--登録したユーザーとログインユーザーが同じ場合に表示-->
            <div class="p-detail__submit" v-else-if="this.userId !== null && Number(this.step['user_id']) ===  Number(this.userId)">
                <button class="c-button p-detail__button p-detail__button--noPadding">
                    <RouterLink class="p-detail__buttonlink" :to="`/edit/${id}`">
                                編集する
                    </RouterLink>
                </button>
            </div>

            <!--ログインしていない場合に表示-->
            <div class="p-detail__submit u-rightMargin__none" v-else>
                <p class="p-detail__alert">
                    STEPに挑戦するためにはユーザー登録・ログインを行ってください。
                </p>
            </div>

        </div>
    </div>
</template>

<script>

    export default {
        props: {
            id: {
                type: String,
                required: true,
            },
            keyword: {
                type: String,
                default : null,
            },
            categoryMain: {
                type: String,
                default : null,
            }
        },
        data: function(){
            return{
                step: null,
                userId: this.$store.getters['auth/userId'],
                backPaginationNum: this.$store.getters['step/currentPage'],
                challengeFlg:false,
                clearFlg:false,
                myChallenge:[],
                userIcon:null,
                userIntroduction:null,
                prevRoute:this.$store.getters['route/getPrevRoute'],
                prevPath:this.$store.getters['route/getPrevPath'],
            }
        },
        methods: {
            //STEP詳細を取得する
            async fetchStep () {
                await this.$store.dispatch('step/fetchStep',this.id)

                //apiStatusがtrue（200OK 取得に成功）であれば、
                if(this.apiStatus) {
                    this.step = this.$store.getters['step/stepDetail']

                   //取得したSTEP詳細から、ログイン中ユーザーの挑戦状況を取得する
                   this.myChallenge = this.step['challenge_step'].filter(function(object){
                        if(object.user_id === this){
                            return true
                        }
                   },this.userId)

                   //挑戦状況のデータがあれば、
                   if(this.myChallenge.length !== 0){
                        //challengeFlgをtrueにする
                        this.challengeFlg = true

                        //取得した挑戦状況（challenge_step）には、親元のSTEPに挑戦したデータ（orderが0）と、
                        //親元のSTEPに紐づくサブSTEPの挑戦データ（orderが0でない）がある
                        //そのため、このSTEPに紐づくサブSTEPが全てクリアされているか確認するために、
                        //order=0のデータのclear_flgを確認し、clear_flgが1(全てクリアしている）であれば、
                        //this.clearFlgをtrueに変更し、このSTEPがクリアされているフラグを立てる
                        for(let i=0;i<this.myChallenge.length;i++){
                            if(this.myChallenge[i]['order'] === 0 && this.myChallenge[i]['clear_flg'] === 1){
                                this.clearFlg = true
                            }
                        }
                   }

                   this.userIcon = (this.step['user']['icon'] === null)?'/images/icon_user.svg':'/storage/'+this.step['user']['icon']
                   this.userIntroduction = (this.step['user']['introduction'] === null)?'自己紹介はまだ登録されていません。':this.step['user']['introduction']
                }
            },
            //STEPに挑戦する
            async challengeStep(mainId,subId){
                const response = await this.$store.dispatch('step/challenge',{step_id: mainId, substep_id:subId})

                //apiStatusがtrue（挑戦に成功）の場合は、
                if(this.apiStatus){
                    //マイページへ遷移し、挑戦一覧に挑戦したSTEPが追加される
                    this.$router.push('/mypage')
                }
            },
            //STEPをTwitterでシェアする
            shareTwitter(){
                console.log(process.env.MIX_APP_ENV)
                //開発環境か本番環境かでurlを分岐させる
                if(process.env.MIX_APP_ENV === 'local'){
                    console.log('local mode')
                    //シェア用の画面を設定
                    const shareURL = 'https://twitter.com/intent/tweet?text=' + 'STEP：'+this.step.title +
                    "%20%STEP%20%STEPをはじめよう" + '&url=' + "http://localhost:8000/steps/"+this.id;
                    //シェア用の画面へ遷移
                    window.open('https://twitter.com/intent/tweet?text=' + 'STEP：'+this.step.title+ "%20%23STEP%20%23STEPをはじめよう" + '&url=' + "http://localhost:8000/steps/"+this.id,'_blank')
                }else{
                    console.log('production mode')
                    const shareURL = 'https://twitter.com/intent/tweet?text=' + 'STEP：'+this.step.title +
                    "%20%STEP%20%STEPをはじめよう" + '&url=' + "https://step-steps.com/steps/"+this.id;
                    window.open('https://twitter.com/intent/tweet?text=' + 'STEP：'+this.step.title+ "%20%23STEP%20%23STEPをはじめよう" + '&url=' + "https://step-steps.com/steps/"+this.id,'_blank')

                }
            }
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
        },
        watch:{
            //$routeの監視
            $route: {
                async handler () {
                    await this.fetchStep()
                },
                immediate: true
            },
        },
    }

</script>
