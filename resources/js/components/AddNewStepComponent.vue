<template>
    <div id="l-siteWidth">
        <!--新規STEP登録画面-->
        <div class="p-newStep">

                <h2 class="c-ornament p-newStep__title">
                    <span class="c-ornament__border p-newStep__border">
                        1. STEP概要の登録
                    </span>
                </h2>

                <!--親元のSTEPを登録するフォーム-->
                <form action="" class="c-form p-newStep__form" @submit.prevent="createStep">
                        <!--STEPタイトル入力部分-->
                        <label for="title" class="c-label p-newStep__label">
                            タイトル
                        </label>
                        <input id="title" type="text" name="title" class="c-input p-newStep__input" v-bind:disabled="createSubStepFlg"
                                placeholder="タイトルを入力してください" v-model="stepForm.title"
                                :class="[createMainStepFlg ? '':'p-newStep__input--invalid',(registerErrors !== null && registerErrors.title) ? 'c-input--error' : '']">
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error p-newStep__error">
                            <ul v-if="registerErrors.title" class="c-error__ul p-newStep__errorUl">
                                <li class="c-error__list p-newStep__errorList" v-for="msg in registerErrors.title" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--メインカテゴリー選択部分-->
                        <label for="categoryMain" class="c-label p-newStep__label">
                            メインカテゴリー
                        </label>
                        <select name="categoryMain" id="categoryMain" class="c-input p-newStep__select" v-model="selectedCategoryMain" v-bind:disabled="createSubStepFlg"
                                :class="[createMainStepFlg ? '':'p-newStep__input--invalid',(registerErrors !== null && registerErrors.category_main) ? 'c-input--error' : '']">
                            <option value="メインカテゴリーを選択してください" class="p-newStep__option p-newStep__option--disable" disabled>
                                メインカテゴリーを選択してください
                            </option>
                            <option :value="category" class="p-newStep__option" v-for="category in categoryListMain"
                                    :key="category">
                                    {{ category }}
                            </option>
                        </select>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error p-newStep__error">
                            <ul v-if="registerErrors.category_main" class="c-error__ul p-newStep__errorUl">
                                <li class="c-error__list p-newStep__errorList" v-for="msg in registerErrors.category_main" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--サブカテゴリー選択部分-->
                        <label for="categorySub" class="c-label p-newStep__label">
                            サブカテゴリー
                        </label>
                        <select name="categorySub" id="categorySub" class="c-input p-newStep__select" v-model="stepForm.category_sub" v-bind:disabled="createSubStepFlg"
                                :class="[createMainStepFlg ? '':'p-newStep__input--invalid',(registerErrors !== null && registerErrors.category_sub) ? 'c-input--error' : '']">
                            <option value="サブカテゴリーを選択してください" class="p-newStep__option p-newStep__option--disable" disabled>
                                サブカテゴリーを選択してください
                            </option>
                            <option :value="category" class="p-newStep__option" v-for="category in categoryListSubSelected"
                                    :key="category">
                                    {{ category }}
                            </option>
                        </select>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error p-newStep__error">
                            <ul v-if="registerErrors.category_sub" class="c-error__ul p-newStep__errorUl">
                                <li class="c-error__list p-newStep__errorList" v-for="msg in registerErrors.category_sub" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--STEP紹介文入力部分-->
                        <label for="content" class="c-label p-newStep__label">
                            STEP紹介文※500字以内
                        </label>
                        <textarea name="content" id="content" cols="30" rows="10" class="c-input p-newStep__textarea"
                                  v-model="stepForm.content" v-bind:disabled="createSubStepFlg" placeholder="500文字以内の自己紹介文を入力できます。"
                                  :class="[createMainStepFlg ? '':'p-newStep__input--invalid',(registerErrors !== null && registerErrors.content) ? 'c-input--error' : '']">
                        </textarea>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error p-newStep__error">
                            <ul v-if="registerErrors.content" class="c-error__ul p-newStep__errorUl">
                                <li class="c-error__list p-newStep__errorList" v-for="msg in registerErrors.content" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--親元のSTEPを登録するボタン-->
                        <div class="c-submit p-newStep__submit">
                            <button class="c-button p-newStep__button"
                            :class="createMainStepFlg ? '':'p-newStep__button--invalid' " v-bind:disabled="createSubStepFlg">
                                STEP概要登録
                            </button>
                        </div>
                </form>

                <!--親元のSTEPに紐づくサブSTEPを登録する部分-->
                <transitionGroup name="fadeSoon" tag="div">
                    <h2 class="c-ornament p-newStep__title" v-if="createSubStepFlg" key="p-newStep__title">
                        <span class="c-ornament__border p-newStep__border">
                            2. サブSTEPの登録
                        </span>
                    </h2>

                    <form key="p-newStep__form" class="c-form p-newStep__form" v-if="createSubStepFlg" @submit.prevent="createSubStep">
                            <!--一つずつのサブSTEP登録パネル部分-->
                            <transitionGroup name="fadeSoon" tag="div">
                                <div class="p-subStep" v-for="object in subStepArray" :key="object">
                                    <!--サブSTEP登録パネルヘッダー-->
                                    <div class="p-subStep__head">
                                        <p class="p-subStep__icon p-subStep__icon--disable">✕</p>
                                        <h3 class="p-subStep__title">STEP {{ subStepArray.indexOf(object)+1 }}</h3>
                                        <p class="p-subStep__icon" @click="removeSubStep(subStepArray.indexOf(object))">✕</p>
                                    </div>
                                    <!--サブSTEPタイトル入力部分-->
                                    <label :for="'subTitle'+(subStepArray.indexOf(object)+1)" class="c-label p-subStep__label">
                                        タイトル
                                    </label>
                                    <input :id="'subTitle'+(subStepArray.indexOf(object)+1)" type="text" :name="'subTitle'+(subStepArray.indexOf(object)+1)"
                                           class="c-input p-subStep__input" v-model="subStepForm[subStepArray.indexOf(object)].subTitle"
                                           placeholder="タイトルを入力してください" :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subTitle']) ? 'c-input--error' : ''">
                                    <!--サブSTEP内容入力部分-->
                                    <label :for="'subContent'+(subStepArray.indexOf(object)+1)" class="c-label p-subStep__label">
                                        内容※500字以内
                                    </label>
                                    <textarea :name="'subContent'+(subStepArray.indexOf(object)+1)" :id="'subContent'+(subStepArray.indexOf(object)+1)"
                                              cols="20" rows="10" class="c-input p-subStep__textarea" v-model="subStepForm[subStepArray.indexOf(object)].subContent"
                                              :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subContent']) ? 'c-input--error' : ''"
                                              placeholder="500文字以内で入力できます。">
                                    </textarea>
                                    <!--サブSTEP目安達成時間選択部分-->
                                    <label :for="'subTime'+(subStepArray.indexOf(object)+1)" class="c-label p-subStep__label">
                                        目安達成時間
                                    </label>
                                    <select :name="'subTime'+(subStepArray.indexOf(object)+1)" :id="'subTime'+(subStepArray.indexOf(object)+1)"
                                            class="c-input p-subStep__select" v-model="subStepForm[subStepArray.indexOf(object)].subTime"
                                            :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subTime']) ? 'c-input--error' : ''">
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

                            <!--サブSTEP追加・登録ボタン部分-->
                            <div class="c-submit p-subStep__submit" key="p-subStep__submit">
                                <p class="c-button p-subStep__button p-subStep__button--black" @click="addSubStep" v-if="subStepArray.length < 20">
                                    サブSTEP追加
                                </p>
                                <p v-else>
                                    <!--既にサブSTEPが20ある場合、サブSTEP追加のボタンは非表示-->
                                </p>

                                <button class="c-button p-subStep__button p-subStep__button--orange" v-if="subStepArray.length > 0">
                                    STEP登録
                                </button>
                            </div>

                            <!--サブSTEPのバリデーションエラー表示部分-->
                            <div v-if="registerErrors" class="c-error p-newStep__error">
                                <ul v-for="array,idx in registerErrors" class="c-error__ul p-newStep__errorUl" :key="idx">
                                    <li class="c-error__list p-newStep__errorList" v-for="msg in array" :key="msg">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        {{ msg }}
                                    </li>
                                </ul>
                            </div>
                    </form>
                </transitionGroup>
        </div>

    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                stepForm: {
                    title: '',
                    category_main: this.selectedCategoryMain,
                    category_sub:'',
                    content:'',
                },
                subStepForm: [{subTitle:'',subContent:'', subTime: null, order:null}],
                createMainStepFlg:true,
                createSubStepFlg:false,
                countAddSubStep: 1,
                subStepArray: [1],
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
            //新しいSTEP（親元）を登録する
            async createStep() {
                //v-modelで紐づいていないメインカテゴリーのみselectedCategoryMainの値を代入する
                if(this.selectedCategoryMain !== 'メインカテゴリーを選択してください'){
                    this.stepForm.category_main = this.selectedCategoryMain
                }
                await this.$store.dispatch('step/create', this.stepForm)

                //親元のSTEPの登録が成功したとき、（apiStatusがtrueのとき）
                if(this.apiStatus) {
                    //STEP概要の登録を不可にし、サブSTEPの入力フォームを表示させる
                    this.createMainStepFlg = false
                    this.createSubStepFlg = true
                    //バリデーションエラーの表示がされている場合は、表示をクリア
                    this.clearError()
                }
            },
            //新しいサブSTEPを追加する
            addSubStep() {
                //subStepArray変数に、一意の数字をpushする
                //配列の個数が増えるたびにv-forのループ数が増えていく（v-forのkeyは、pushした一意の数字）
                //一意の数字を与えることによってinputの内容とkeyを紐づける
                this.countAddSubStep = this.countAddSubStep + 1
                this.subStepArray.push(this.countAddSubStep)

                //追加したサブSTEP用のオブジェクトを、subStepForm配列に追加する
                this.subStepForm.push({subTitle:'',subContent:'', subTime: null})
            },
            //追加したサブSTEPを削除する
            removeSubStep(number) {
                //引数で、削除する配列要素のインデックス番号を取得し、要素を削除
                //この時、配列のkeyに一意の数字が与えられているため、inputの内容が保持される
                this.subStepArray.splice(number,1)

                //削除したサブSTEP用のオブジェクトを配列から削除する
                this.subStepForm.splice(number,1)
            },
            //サブカテゴリーの表示項目を変更する
            changeSubCategory(category){
                //既に選択されているサブカテゴリーを空にする
                this.stepForm.category_sub = null

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
            //サブSTEPを登録する
            async createSubStep(){
                //サブSTEPの順番（order）をsubStepForm配列に追加する
                for(let i = 0; i<this.subStepForm.length; i++){
                    this.subStepForm[i]["order"] = i+1
                }

                await this.$store.dispatch('step/createSubStep', this.subStepForm)

                //サブSTEPの登録が成功した場合、
                if(this.apiStatus) {
                    //マイページへ遷移する
                    this.$router.push('/mypage')
                }
            },
            //validationエラーを空にする
            clearError () {
                     this.$store.commit('step/setRegisterErrorMessages', null)
            },
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.step.apiStatus
            },
            //registerErrorMessagesステートを参照する
            registerErrors () {
                 return this.$store.state.step.registerErrorMessages
            },
        },
        created () {
             //ページcreate時にバリデーションエラーをリセットする
             this.clearError()
        },
        watch: {
            //メインカテゴリーの変更を監視
            selectedCategoryMain: {
                handler: function(newData, oldData){
                //メインカテゴリーの変更が感知されたら、changeSubCategoryを呼び出しサブカテゴリーを変更する
                this.changeSubCategory(newData)
                },
                deep:true,
                immediate:true,
            }
        },
    }

</script>
