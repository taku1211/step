<template>
    <div id="l-main--siteWidth">
        <!--サブSTEP詳細ページ-->
        <div class="p-subDetail" v-if="subStep">
            <RouterLink :to="`/steps/${stepMainId}`" class="p-subDetail__link">
                STEP詳細ページへ戻る
            </RouterLink>
            <h2 class="c-ornament p-subDetail__title">
                <span class="c-ornament__border p-subDetail__border">
                    <span class="p-subDetail__budge">
                        STEP {{ subStep.order }}
                    </span>
                    {{ subStep.title}}
                </span>
            </h2>
            <div class="p-subDetail__imageArea">
                <img :src="subStep.step.image_path" alt="STEPアイキャッチ画像" class="p-subDetail__image">
            </div>
            <!--サブSTEP詳細の表示-->
            <div class="p-subDetail__flex">
                <div class="p-subDetail__left">
                    <!--サブSTEPの概要表示（ページ左側）-->
                    <!--widthが768px以下の場合は、ページ上側-->
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢サブSTEPタイトル
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--leftMargin">
                        {{ subStep.title }}
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢カテゴリー
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--leftMargin">
                        {{ subStep.step.category_main }} | {{ subStep.step.category_sub }}
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢目安達成時間
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--leftMargin">
                        {{ (Math.floor(subStep.time_aim/60) !== 0)?Math.floor(subStep.time_aim/60)+'時間':'' }}
                        {{ (subStep.time_aim%60 !== 0)?(subStep.time_aim%60)+'分':'' }}
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢サブステップ概要
                    </p>
                    <p class="p-subDetail__para p-subDetail__para--leftMargin">
                        {{ (subStep.content !== null)? subStep.content : '概要は登録されていません。' }}
                    </p>
                </div>
                <!--親元のSTEPに紐づくその他のサブSTEP表示（ページ右側）-->
                <!--widthが768px以下の場合は、ページ下側-->

                <!--登録したユーザーとログインユーザーが異なる、もしくはログイン状態ではない場合に表示-->
                <div class="p-subDetail__right" v-if="Number(this.subStep['user_id']) !==  Number(this.userId)">
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢{{ subStep.step.title }}
                    </p>
                    <div class="p-subDetail__sub" v-for="(relatedSubStep, idx) in relatedSubSteps" :key="idx">
                        <p class="p-subDetail__subPara p-subDetail__subPara--left">
                            STEP{{ relatedSubStep.order }}
                        </p>
                        <RouterLink :to="`/substeps/${relatedSubStep.id}`" class="p-subDetail__subPara p-subDetail__subPara--center">
                            <p class="p-subDetail__subTitle">
                                {{ relatedSubStep.title }}
                            </p>
                        </RouterLink>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済ではない、かつ、このサブSTEPに挑戦済の場合-->
                        <p class="p-subDetail__subPara p-subDetail__subPara--right p-subDetail__subPara--orange"
                           v-if="myChallenge && Array(myChallenge) && myChallenge.length !== 0 && myChallenge[idx+1]['clear_flg'] === 0 && myChallenge[idx+1]['challenge_flg'] === 1">
                           チャレンジ中
                        </p>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済、かつ、このサブSTEPに挑戦済の場合-->
                        <p class="p-subDetail__subPara p-subDetail__subPara--right p-subDetail__subPara--green"
                           v-else-if="myChallenge && Array(myChallenge) && myChallenge.length !== 0 && myChallenge[idx+1]['clear_flg'] === 1 && myChallenge[idx+1]['challenge_flg'] === 1">
                           クリア
                        </p>
                        <!--STEPに挑戦済、かつ、このサブSTEPがクリア済みではない、かつ、このサブSTEPにまだ挑戦していない場合-->
                        <p class="p-subDetail__subPara p-subDetail__subPara--right" v-else>
                            <i class="fa-solid fa-lock"></i>
                        </p>
                    </div>
                </div>
                <!--登録したユーザーとログインユーザーが同じ場合-->
                <div class="p-subDetail__right" v-else>
                    <p class="p-subDetail__para p-subDetail__para--title">
                        ♢{{ subStep.step.title }}
                    </p>
                    <div class="p-subDetail__sub" v-for="(relatedSubStep, idx) in relatedSubSteps" :key="idx">
                        <p class="p-subDetail__subPara p-subDetail__subPara--left">
                            STEP{{ relatedSubStep.order }}
                        </p>
                        <RouterLink :to="`/substeps/${relatedSubStep.id}`" class="p-subDetail__subPara p-subDetail__subPara--center">
                            <p class="p-subDetail__subTitle">
                                {{ relatedSubStep.title }}
                            </p>
                        </RouterLink>
                        <!--チャレンジ中・クリアなどを表示するのではなく、各サブSTEPの目安達成時間を表示する-->
                        <p class="p-subDetail__subPara p-subDetail__subPara--right">
                            {{ (Math.floor(relatedSubStep.time_aim/60) !== 0)?Math.floor(relatedSubStep.time_aim/60)+'時間':'' }}
                            {{ (relatedSubStep.time_aim%60 !== 0)?(relatedSubStep.time_aim%60)+'分':'' }}
                        </p>
                    </div>
                </div>
            </div>

            <!--このサブSTEPをクリア・かかった時間を登録・STEPを編集するための部分-->
            <!--ログイン状態の場合に表示-->
            <div class="p-subDetail__submit" v-if="userId !== null">

                <!--目標達成にかかった時間を登録・更新する部分-->
                <!--すでに挑戦済みの場合に表示する-->
                <div class="p-subDetail__block" v-if="myChallenge && Array(myChallenge) && myChallenge.length !== 0 && myChallenge[subStep.order]['challenge_flg'] === 1">
                    <label for="subTime" class="c-label p-subDetail__label">
                        目標達成にかかった時間：
                    </label>
                    <select name="subTime" id="subTime" class="c-input p-subDetail__select" v-model="time">
                        <option value="15">15分</option>
                        <option value="30">30分</option>
                        <option value="60">1時間</option>
                        <option value="90">1時間30分</option>
                        <option value="120">2時間</option>
                        <option value="180">3時間</option>
                        <option value="240">4時間</option>
                        <option value="300">5時間</option>
                        <option value="360">6時間</option>
                        <option value="720">12時間</option>
                        <option value="1440">1日</option>
                        <option value="2880">2日</option>
                        <option value="4320">3日</option>
                        <option value="5760">4日</option>
                        <option value="7200">5日</option>
                        <option value="8640">6日</option>
                        <option value="10080">1週間</option>
                        <option value="20160">2週間</option>
                        <option value="30240">3週間</option>
                        <option value="40320">4週間</option>
                    </select>
                </div>

                <!--このSTEPを登録したユーザーとログインユーザーが同じ場合-->
                <button class="c-button p-subDetail__button p-subDetail__button--noPadding" v-if="Number(this.subStep['user_id']) ===  Number(this.userId)">
                    <RouterLink class="p-subDetail__buttonlink" :to="`/edit/${this.stepMainId}`">
                                編集する
                    </RouterLink>
                </button>
                <!--このサブSTEPに挑戦済みで、かつまだクリアしていない場合-->
                <button class="c-button c-button--orange p-subDetail__button" @click="clearSubStep(id)"
                        v-else-if="Number(this.subStep['user_id']) !==  Number(this.userId) && myChallenge && Array(myChallenge) && myChallenge.length !== 0 &&
                        myChallenge[subStep.order]['challenge_flg'] === 1 && myChallenge[subStep.order]['clear_flg'] === 0">
                    クリアする
                </button>
                <!--このサブSTEPに挑戦済みで、かつまだクリア済の場合-->
                <button class="c-button p-subDetail__button p-subDetail__button--green" @click="updateClearTime(id)"
                        v-else-if="Number(this.subStep['user_id']) !==  Number(this.userId) && myChallenge && Array(myChallenge) && myChallenge.length !== 0 &&
                        myChallenge[subStep.order]['challenge_flg'] === 1 && myChallenge[subStep.order]['clear_flg'] === 1">
                    時間を更新
                </button>
                <!--このサブSTEPに挑戦していない場合-->
                <button class="c-button p-subDetail__button p-subDetail__button--gray" disabled v-else>
                    <i class="fa-solid fa-lock"></i>
                </button>
            </div>

            <!--ログインしていない場合に表示-->
            <div class="p-subDetail__footer u-rightMargin__none" v-else>
                <p class="p-subDetail__alert">
                    STEPに挑戦するためには<span class="u-showMd"><br></span>ユーザー登録・ログインを行ってください。
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { OK } from '../util'
import mainCategoryJson from "./../../json/categoryList.json"

    export default {
        props: {
            id: {
                type: String,
                required: true,
            },
        },
        data: function() {
            return {
                allSubSteps: null,
                subStep: null,
                stepMainId:null,
                relatedSubSteps:[],
                allChallenge:[],
                myChallenge:[],
                userId:this.$store.getters['auth/userId'],
                time:15,
                categoryList:mainCategoryJson['mainCategory']
            }
        },
        methods: {
            //全てのサブSTEPを取得する
            async fetchAllSubSteps() {
                const response = await axios.get('/api/substeps')

                if (response.status !== OK) {
                        this.$store.commit('error/setCode', response.status)
                        return false
                }
                //取得したデータをallSubStepsに格納
                this.allSubSteps = response.data
            },
            //選択されたサブSTEPを取得する
            getSelectedSubStep(){
                this.allSubSteps.forEach(element => {
                    if(element['id'] === Number(this.id)){
                        //subStepに選択されたサブSTEPを格納
                        this.subStep = element

                        //アイキャッチ画像のpathを取得
                        if(this.subStep.step["image_path"] === null){
                            this.subStep.step["image_path"] = "/images/category-image-" + this.subStep.step["category_main"] + ".jpg"
                        }else{
                            this.subStep.step["image_path"] ="/storage/" + this.subStep.step["image_path"]
                        }

                        //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
                        this.subStep.step["category_sub"] = this.categoryList[this.subStep.step['category_main'] - 1]['subCategory'][this.subStep.step['category_sub'] - 1]['name']
                        this.subStep.step["category_main"] = this.categoryList[this.subStep.step['category_main'] - 1]['name']

                        //選択されたサブSTEPの親STEPのidを取得し格納
                        this.stepMainId = this.subStep.step_id
                    }
                })
            },
            //同じ親元のSTEPに紐づいているサブSTEPを取得する
            getRelatedSubSteps(){
                //relatedSubStepsを空配列にする
                this.relatedSubSteps = []

                this.allSubSteps.forEach(element => {
                    if(element['step_id'] === Number(this.stepMainId) ){
                        this.relatedSubSteps.push(element)

                        //取得したサブSTEPを、サブSTEPのステップ順（order順）に並び替える
                        this.relatedSubSteps.sort(function(prev, next){
                            return prev.order - next.order
                        })
                    }
                })
            },
            //親元のSTEPに挑戦しているデータをすべて取得し、自分の挑戦データを抽出
            async getChallengeData(stepMainId){
                const response = await axios.get(`/api/steps/${stepMainId}`)

                if (response.status !== OK) {
                        this.$store.commit('error/setCode', response.status)
                        return false
                }
                this.allChallenge = response.data['challenge_step']

                //取得したデータのうち、自分が挑戦しているデータを取得する
                this.myChallenge = this.allChallenge.filter(function(object){
                    if(object.user_id === this){
                        return true
                    }
                },this.userId)

                //挑戦しているデータが存在し、かつクリア済の場合（timeが更新されており、0ではない場合）
                if(this.myChallenge && Array(this.myChallenge) && this.myChallenge.length !== 0  && this.myChallenge[this.subStep['order']]['time'] !== 0){
                    //timeに取得した、取り組んだ時間を格納
                    this.time = this.myChallenge[this.subStep['order']]['time']

                //クリア済みではない場合
                }else{
                    //デフォルトの値である15分を格納
                    this.time = 15
                }
            },
            //サブSTEPをクリアする
            async clearSubStep(id){
                const response = await axios.post('/api/clear',{id:Number(id),time:Number(this.time),order:Number(this.subStep['order']),mainId:Number(this.stepMainId)})

                if(response.status !== OK){
                    //エラーコードの種類に応じて、component側でエラー画面を表示させる
                    this.$store.commit('error/setCode', response.status)
                    if(response.data.message){
                        this.$store.commit('message/setContent', {
                            content: response.data.message,
                            timeout: 5000
                        },{root:true})
                        this.$store.commit('message/setDangerFlg', {
                            boolean : true,
                            timeout: 5000
                            },{root:true})
                    }
                    return false
                }

                //クリアしたサブSTEPが、親元のSTEPの最後のサブSTEPの場合、
                if(Number(this.subStep['order']) === Number(this.relatedSubSteps.length)){
                    this.$store.commit('message/setContent', {
                        content: 'このSTEPを全てクリアしました！',
                        timeout: 5000
                    } ,{root:true})
                    //親元のSTEPの詳細ページへ遷移させる
                    this.$router.push(`/steps/${this.stepMainId}`)

                //クリアしたサブSTEPの後に、まだ未挑戦のサブSTEPがある場合、
                }else{
                    this.$store.commit('message/setContent', {
                        content: 'サブSTEPをクリアしました！次のサブSTEPに挑戦しましょう！',
                        timeout: 5000
                    } ,{root:true})
                    //次のサブSTEPの詳細ページへ遷移させる
                    this.$router.push(`/substeps/${this.relatedSubSteps[this.subStep['order']]['id']}`)
                }
            },
            //既にクリアしたサブSTEPの取り組んだ時間を更新する
            async updateClearTime(id){
                const response = await axios.post('/api/updateClear',{id:Number(id),time:Number(this.time),mainId:Number(this.stepMainId)})

                if(response.status !== OK){
                    this.$store.commit('error/setCode', response.status)
                    this.$store.commit('message/setContent', {
                        content: response.data.message,
                        timeout: 5000
                        } ,{root:true})
                    this.$store.commit('message/setDangerFlg', {
                        boolean : true,
                        timeout: 5000
                        }, {root:true})
                    return false
                }
                this.$store.commit('message/setContent', {
                        content: 'サブSTEPに取り組んだ時間を更新しました！',
                        timeout: 5000
                } ,{root:true})

                //親元のSTEPの詳細ページへ遷移させる
                this.$router.push(`/steps/${this.stepMainId}`)
            },
        },
        watch: {
            //$routeの監視
            $route: {
                async handler () {
                    await this.fetchAllSubSteps()
                    this.getSelectedSubStep()
                    this.getRelatedSubSteps()
                    this.getChallengeData(this.stepMainId)
                },
                immediate: true
            }
        }
    }

</script>
