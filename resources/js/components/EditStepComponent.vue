<template>
    <div id="l-siteWidth">
        <!--STEP編集画面-->
        <div class="p-editStep">
                <h2 class="c-ornament p-editStep__title">
                    <span class="c-ornament__border p-editStep__border">
                        1. STEP概要の更新
                    </span>
                </h2>

                <!--既に編集するSTEPに挑戦しているユーザーがいる場合、以下を表示-->
                <p class="c-message p-editStep__message" v-if="challengedFlg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    既にチャレンジ中のユーザーがいるためサブSTEPを追加・削除はできません。<br>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    既にチャレンジ中のユーザーは、STEPが削除されてもチャレンジを続けることができます。
                </p>

                <!--STEP削除ボタン部分-->
                <div class="c-submit p-editStep__submit">
                    <button class="c-button p-editStep__button" @click="deleteStep">STEPを削除</button>
                </div>

                <!--STEP編集フォーム部分-->
                <form action="" class="c-form p-editStep__form" @submit.prevent="editStep">

                    <!--親元のSTEPを編集するフォーム-->
                    <div class="p-editStep__mainStep">
                        <!--タイトル入力部分-->
                        <label for="title" class="c-label p-editStep__label">
                            タイトル
                        </label>
                        <input id="title" type="text" name="title" class="c-input p-editStep__input"
                               placeholder="タイトルを入力してください" v-model="stepForm.title"
                               :class="(updateErrors !== null && updateErrors.title) ? 'c-input--error' : ''">
                        <!--メインカテゴリー選択部分-->
                        <label for="categoryMain" class="c-label p-editStep__label">
                            メインカテゴリー
                        </label>
                        <select name="categoryMain" id="categoryMain" class="c-input p-editStep__select" v-model="selectedCategoryMain"
                                :class="(updateErrors !== null && updateErrors.category_main) ? 'c-input--error' : ''">
                            <option value="メインカテゴリーを選択してください" class="p-editStep__option p-editStep__option--disable" disabled>
                                メインカテゴリーを選択してください
                            </option>
                            <option :value="category" class="p-newStep__option" v-for="category in categoryListMain"
                                    :key="category">{{ category }}
                            </option>
                        </select>
                        <!--サブカテゴリー選択部分-->
                        <label for="categorySub" class="c-label p-editStep__label">
                            サブカテゴリー
                        </label>
                        <select name="categorySub" id="categorySub" class="c-input p-editStep__select" v-model="stepForm.category_sub"
                                :class="(updateErrors !== null && updateErrors.category_sub) ? 'c-input--error' : ''">
                            <option value="サブカテゴリーを選択してください" class="p-editStep__option p-editStep__option--disable" disabled>
                                サブカテゴリーを選択してください
                            </option>
                            <option :value="category" class="p-editStep__option" v-for="category in categoryListSubSelected"
                                    :key="category">{{ category }}
                            </option>
                        </select>
                        <!--STEP紹介文入力部分-->
                        <label for="content" class="c-label p-newStep__label">STEP紹介文※500字以内</label>
                        <textarea name="content" id="content" cols="30" rows="10" class="c-input p-newStep__textarea"
                                  v-model="stepForm.content" placeholder="500文字以内の自己紹介文を入力できます。"
                                  :class="(updateErrors !== null && updateErrors.content) ? 'c-input--error' : ''">
                        </textarea>
                    </div>

                    <!--親元のSTEPに紐づくサブSTEPを編集するフォーム-->
                    <h2 class="c-ornament p-editStep__title">
                        <span class="c-ornament__border p-editStep__border">
                            2. サブSTEPの更新
                        </span>
                    </h2>
                        <!--一つずつのサブSTEP編集パネル部分-->
                        <transitionGroup name="fadeSoon" tag="div">
                            <div class="p-editSubStep" v-for="object in subStepArray" :key="object">
                                <!--サブSTEP登録パネルヘッダー-->
                                <div class="p-editSubStep__head">
                                    <p class="p-editSubStep__icon p-editSubStep__icon--disable">✕</p>
                                    <h3 class="p-editSubStep__title">STEP {{ subStepArray.indexOf(object)+1 }}</h3>
                                    <p class="p-editSubStep__icon" @click="removeSubStep(subStepArray.indexOf(object))" v-if="!challengedFlg">✕</p>
                                    <p class="p-editSubStep__icon p-editSubStep__icon--disable" v-else>✕</p>
                                </div>

                                <!--サブSTEPタイトル入力部分-->
                                <label :for="'subTitle'+(subStepArray.indexOf(object)+1)" class="c-label p-editSubStep__label">
                                    タイトル
                                </label>
                                <input :id="'subTitle'+(subStepArray.indexOf(object)+1)" type="text" :name="'subTitle'+(subStepArray.indexOf(object)+1)"
                                       class="c-input p-editSubStep__input" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].title"
                                       :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.title']) ? 'c-input--error' : ''">
                                <!--サブSTEP内容入力部分-->
                                <label :for="'subContent'+(subStepArray.indexOf(object)+1)" class="c-label p-editSubStep__label">
                                    内容※500字以内
                                </label>
                                <textarea :name="'subContent'+(subStepArray.indexOf(object)+1)" :id="'subContent'+(subStepArray.indexOf(object)+1)"
                                          cols="20" rows="10" class="c-input p-editSubStep__textarea" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].content"
                                          :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.content']) ? 'c-input--error' : ''">
                                </textarea>
                                <!--サブSTEP目安達成時間選択部分-->
                                <label :for="'subTime'+(subStepArray.indexOf(object)+1)" class="c-label p-editSubStep__label">
                                    目安達成時間
                                </label>
                                <select :name="'subTime'+(subStepArray.indexOf(object)+1)" :id="'subTime'+(subStepArray.indexOf(object)+1)"
                                        class="c-input p-editSubStep__select" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].time_aim"
                                        :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.time_aim']) ? 'c-input--error' : ''">
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
                        </transitionGroup>

                        <!--サブSTEP追加・STEP更新ボタン部分-->
                        <div class="c-submit p-editSubStep__submit">
                            <p class="c-button p-editSubStep__button p-editSubStep__button--black" @click="addSubStep" v-if="!challengedFlg && subStepArray.length < 20">
                                サブSTEP追加
                            </p>
                            <p v-else>
                                <!--既にサブSTEPが20ある場合、サブSTEP追加のボタンは非表示-->
                            </p>

                            <button class="c-button p-editSubStep__button p-editSubStep__button--orange" >
                                更新する
                            </button>
                        </div>
                        <!--STEP更新時のバリデーションエラー表示部分-->
                        <div v-if="updateErrors" class="c-error p-editStep__error">
                            <ul v-for="array,idx in updateErrors" class="c-error__ul p-editSubStep__errorUl" :key="idx">
                                <li class="c-error__list p-editSubStep__errorList" v-for="msg in array" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>
                </form>
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
        },
        data: function() {
            return {
                step:[],
                stepForm: {
                    id:null,
                    title: '',
                    category_main: '',
                    category_sub:'',
                    content:'',
                    time_aim:0,
                    step_number:0,
                    subStepForm:[],
                    deletedSubStep:[],
                },
                countAddSubStep: 0,
                subStepArray: [],
                challengedFlg: false,
                selectedCategoryMain:'メインカテゴリーを選択してください',
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
            //更新するSTEPをデータから取得する
            async fetchStep () {
                await this.$store.dispatch('step/fetchStep',this.id)

                //apiStatusがtrue（200OK）であれば
                if(this.apiStatus) {
                    this.step = this.$store.getters['step/stepDetail']
                    const loginUserId = this.$store.getters['auth/id']

                    //ログインユーザーのidと取得したデータのuser_idが異なる場合は404NotFoundページへ移動
                    if(Number(this.step['user_id']) !== loginUserId){
                        this.$router.push('/*')
                    }
                    //取得したSTEPについて、他のユーザーが既に挑戦中かで条件分岐
                    //既に挑戦中の場合は、サブステップの追加・削除を不可にする
                    if(this.step['challenge_step'].length !== 0){
                        this.challengedFlg = true
                    }

                    //サブカテゴリーの表示項目を動的に切り替えるために、
                    //登録されているカテゴリーを、カテゴリー表示用変数に格納する
                    this.selectedCategoryMain = this.step['category_main']

                    //post送信用のstepFormに取得したSTEPのデータを格納する
                    this.stepForm['category_main'] = this.step['category_main']
                    this.stepForm['category_sub'] = this.step['category_sub']
                    this.stepForm['title'] = this.step['title']
                    this.stepForm['content'] = this.step['content']
                    this.stepForm['step_number'] = this.step['step_number']
                    this.stepForm['time_aim'] = this.step['time_aim']

                    //取得したSTEPのサブSTEP数に応じて、サブSTEPを編集するための配列を動的に生成
                    if(this.step['substeps'].length !== 0){
                        for(let i=0; i<this.step['substeps'].length; i++){
                            //post送信用のstrwpFormに、サブSTEP更新用の配列を追加する
                            this.stepForm['subStepForm'].push(this.step['substeps'][i])

                            //サブSTEPを表示するためのsubStepArrayを更新する
                            this.subStepArray.push(i+1)
                        }
                        this.countAddSubStep = this.step['substeps'].length
                    }
                }
            },
            //STEPの更新処理
            async editStep(){
                //更新するためのSTEPのidをstepFormに格納
                this.stepForm['id'] = Number(this.step['id'])
                //STEPの目安達成時間の合計を一旦初期化
                this.stepForm['time_aim'] = 0

                //サブSTEPの目安達成時間を合計して、STEPの目安達成時間の合計を計算し追加する
                //サブSTEPの順番を変更・追加する、
                for(let i = 0; i<this.stepForm['subStepForm'].length; i++){
                    this.stepForm['subStepForm'][i]["order"] = i+1
                    this.stepForm['time_aim'] = Number(this.stepForm['time_aim']) + Number(this.stepForm['subStepForm'][i]["time_aim"])
                }

                //サブSTEP数を計算して追加する
                this.stepForm['step_number'] = this.stepForm['subStepForm'].length

                await this.$store.dispatch('step/edit', this.stepForm)

                //STEPの更新が成功したら、
                if(this.apiStatus) {
                    //更新したSTEPのSTEP詳細ページへ遷移する
                    this.$router.push('/steps/'+this.id)
                }
            },
            //新しいサブSTEPを追加する
            addSubStep() {
                //subStepArray変数に、一意の数字を追加する
                //配列の個数が増えるたびにv-forのループ数が増えていく（v-forのkeyは、pushした一意の数字）
                //一意の数字を与えることによってinputの内容とkeyを紐づける
                this.countAddSubStep = this.countAddSubStep + 1
                this.subStepArray.push(this.countAddSubStep)

                //追加したサブSTEP用のオブジェクトを配列に追加する
                this.stepForm['subStepForm'].push({id:null, title:'',content:'', time_aim: null})
            },
            //サブSTEPを削除する
            removeSubStep(number) {
                //削除したサブステップのidがnullでない場合（既にDBに登録されている場合）は、
                if(this.stepForm['subStepForm'][number]['id'] !== null){

                    //コントローラー側で、画面上で削除したサブSTEPをDBから削除するために、
                    //削除したサブSTEPのidを格納するdeletedSubStepに追加する
                    this.stepForm.deletedSubStep.push(this.stepForm['subStepForm'][number]['id'])
                }

                //引数で削除する配列要素のインデックス番号を取得し、要素を削除
                //この時、配列の要素に一意の数字が与えられているため、inputの内容が保持される
                this.subStepArray.splice(number,1)

                //削除したサブSTEP用のオブジェクトを配列から削除する
                this.stepForm['subStepForm'].splice(number,1)
            },
            //サブカテゴリーの表示項目を変更する
            changeSubCategory(category){

                //サブカテゴリーの表示を変更する前に、stepFormのメインカテゴリーの値とselectedCategoryMainの値を比べる
                //もし上記2つが一致した場合、一致する理由はfetchStepメソッド内で上記2つに、取得した値を代入したためである
                //この場合、サブカテゴリーの値は、null（初期化）せず、取得した値をそのまま表示させる必要がある
                //※初期化してしまうと、DBに登録されているサブカテゴリーと画面に描写されているサブカテゴリーが異なってしまう

                //一方で、上記2つが一致しない場合
                //その理由は、画面描写後、メインカテゴリーのselectBoxの選択を変更したからである
                //そのため、サブカテゴリーは一旦初期化する必要がある（初期化しないとサブカテゴリーの値が不正に残ってしまう）

                if(this.stepForm['category_main'] !== this.selectedCategoryMain){
                    //既に選択されているサブカテゴリーを空にする
                    this.stepForm['category_sub'] = null

                    this.stepForm['category_main'] = this.selectedCategoryMain
                }


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
            //STEPを削除する
            async deleteStep(){
                if(confirm('このSTEPを削除しますか？')){
                    await this.$store.dispatch('step/delete', this.id)

                    //STEPの削除に成功したら、
                    if(this.apiStatus) {
                    //マイページへ遷移する
                    this.$router.push('/mypage')
                }
                }
            },
            //validationエラーを空にする
            clearError () {
                     this.$store.commit('step/setUpdateErrorMessages', null)
            },
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.step.apiStatus
            },
            //updateErrorMessagesステートを参照する
            updateErrors () {
                 return this.$store.state.step.updateErrorMessages
            }
        },
        created () {
            //ページcreate時にバリデーションエラーをリセットする
             this.clearError()
        },
        watch: {
            //メインカテゴリーの変更を監視
            selectedCategoryMain: {
                handler: function(newData, oldData){
                //メインカテゴリーの変更が感知されたら、changeSubCategorを呼び出しサブカテゴリーを変更する
                this.changeSubCategory(newData)
                },
                deep:true,
                immediate:false,
            },
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
