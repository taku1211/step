<template>
    <div id="l-main--siteWidth">
        <!--STEP編集画面-->
        <div class="p-editStep">
                <h2 class="c-ornament p-editStep__title">
                    <span class="c-ornament__border">
                        1. STEP概要の更新
                    </span>
                </h2>

                <!--既に編集するSTEPに挑戦しているユーザーがいる場合、以下を表示-->
                <p class="c-message" v-if="challengedFlg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    既にチャレンジ中のユーザーがいるためサブSTEPを追加・削除はできません。<br>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    既にチャレンジ中のユーザーは、STEPが削除されてもチャレンジを続けることができます。
                </p>

                <!--STEP削除ボタン部分-->
                <div class="c-submit">
                    <button class="c-button p-editStep__button" @click="deleteStep">STEPを削除</button>
                </div>

                <!--STEP編集フォーム部分-->
                <form action="" class="c-form p-editStep__form" @submit.prevent="editStep">

                    <!--親元のSTEPを編集するフォーム-->
                    <div class="p-editStep__mainStep">
                        <!--タイトル入力部分-->
                        <label for="title" class="c-label c-label--marginl">
                            タイトル
                        </label>
                        <input id="title" type="text" name="title" class="c-input"
                               placeholder="タイトルを入力してください" v-model="stepForm.title"
                               :class="(updateErrors !== null && updateErrors.title) ? 'c-input--error' : ''">
                        <!--メインカテゴリー選択部分-->
                        <label for="categoryMain" class="c-label c-label--marginl">
                            メインカテゴリー
                        </label>
                        <select name="categoryMain" id="categoryMain" class="c-select c-select--boxshadow c-select--fullWidth" v-model="selectedCategoryMain"
                                :class="(updateErrors !== null && updateErrors.category_main) ? 'c-select--error' : ''">
                            <option value="メインカテゴリーを選択してください" class="c-select__option c-select__option--disable" disabled>
                                メインカテゴリーを選択してください
                            </option>
                            <option :value="category.id" class="c-select__option" v-for="category in categoryList"
                                    :key="category.id">{{ category.name }}
                            </option>
                        </select>
                        <!--サブカテゴリー選択部分-->
                        <label for="categorySub" class="c-label c-label--marginl">
                            サブカテゴリー
                        </label>
                        <select name="categorySub" id="categorySub" class="c-select c-select--boxshadow c-select--fullWidth" v-model="stepForm.category_sub"
                                :class="(updateErrors !== null && updateErrors.category_sub) ? 'c-select--error' : ''">
                            <option value="サブカテゴリーを選択してください" class="c-select__option c-select__option--disable" disabled>
                                サブカテゴリーを選択してください
                            </option>
                            <option :value="category.id" class="c-select__option" v-for="category in categoryListSubSelected"
                                    :key="category.id">{{ category.name }}
                            </option>
                        </select>
                        <!--STEP紹介文入力部分-->
                        <label for="content" class="c-label c-label--marginl">STEP紹介文※500字以内</label>
                        <textarea name="content" id="content" cols="30" rows="10" class="c-textarea"
                                  v-model="stepForm.content" placeholder="500文字以内の自己紹介文を入力できます。"
                                  :class="(updateErrors !== null && updateErrors.content) ? 'c-textarea--error' : ''">
                        </textarea>
                        <!-- STEPアイキャッチ画像登録部分 -->
                        <label for="image" class="c-label c-label--marginl">
                            STEPアイキャッチ画像※10MB以内<br>
                            <span class="u-fontSizeSmall">※画像を登録しない場合は、カテゴリー毎にデフォルトのアイキャッチ画像を表示します。</span>
                        </label>
                        <!--登録済みのアイキャッチ画像をリセットする部分-->
                        <div class="p-editStep__deleteImage">
                            <p class="c-button p-editStep__button p-editStep__button--reset" @click="deleteRegisterdImage" v-if="stepForm.imageName">
                                画像をリセット
                            </p>
                        </div>
                        <input id="image" type="file" name="image" class="c-input"
                                placeholder="タイトルを入力してください" @change="onFileChange"
                                :class="(updateErrors !== null && updateErrors.image) ? 'c-input--error' : ''">

                        <!--バリデーションエラー部分-->
                        <div v-if="updateErrors" class="c-error">
                                <ul v-if="updateErrors.image" class="c-error__ul">
                                    <li class="c-error__list" v-for="msg in updateErrors.image" :key="msg">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        {{ msg }}
                                    </li>
                                </ul>
                        </div>
                        <!--ファイル選択時のエラー表示部分（ファイル形式・ファイルサイズ）-->
                        <div v-if="sizeErrorMessage" class="c-error">
                                <ul  class="c-error__ul" v-if="sizeErrorMessage">
                                    <li class="c-error__list">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        {{ sizeErrorMessage }}
                                    </li>
                                </ul>
                        </div>
                        <!--アイキャッチ画像プレビュー部分-->
                        <div v-if="preview">
                            <label class="c-label c-label--marginl u-pointerNone">
                                アイキャッチ画像プレビュー
                            </label>
                            <output>
                                <img :src="preview" alt="アップロード画像" class="c-imagePreview">
                            </output>
                        </div>

                    </div>

                    <!--親元のSTEPに紐づくサブSTEPを編集するフォーム-->
                    <h2 class="c-ornament p-editStep__title">
                        <span class="c-ornament__border">
                            2. サブSTEPの更新
                        </span>
                    </h2>
                        <!--一つずつのサブSTEP編集パネル部分-->
                        <transitionGroup name="c-fade" tag="div">
                            <div class="c-subStep" v-for="object in subStepArray" :key="object">
                                <!--サブSTEP登録パネルヘッダー-->
                                <div class="c-subStep__head">
                                    <p class="c-subStep__icon c-subStep__icon--disable">✕</p>
                                    <h3 class="c-subStep__title">STEP {{ subStepArray.indexOf(object)+1 }}</h3>
                                    <p class="c-subStep__icon" @click="removeSubStep(subStepArray.indexOf(object))" v-if="!challengedFlg">✕</p>
                                    <p class="c-subStep__icon c-subStep__icon--disable" v-else>✕</p>
                                </div>

                                <!--サブSTEPタイトル入力部分-->
                                <label :for="'subTitle'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                    タイトル
                                </label>
                                <input :id="'subTitle'+(subStepArray.indexOf(object)+1)" type="text" :name="'subTitle'+(subStepArray.indexOf(object)+1)"
                                       class="c-input c-input--marginBottomL" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].title"
                                       :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.title']) ? 'c-input--error' : ''">
                                <!--サブSTEP内容入力部分-->
                                <label :for="'subContent'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                    内容※500字以内
                                </label>
                                <textarea :name="'subContent'+(subStepArray.indexOf(object)+1)" :id="'subContent'+(subStepArray.indexOf(object)+1)"
                                          cols="20" rows="10" class="c-textarea c-textarea--heightLow" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].content"
                                          :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.content']) ? 'c-textarea--error' : ''">
                                </textarea>
                                <!--サブSTEP目安達成時間選択部分-->
                                <label :for="'subTime'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                    目安達成時間
                                </label>
                                <select :name="'subTime'+(subStepArray.indexOf(object)+1)" :id="'subTime'+(subStepArray.indexOf(object)+1)"
                                        class="c-select c-select--boxshadow c-select--fullWidth" v-model="stepForm.subStepForm[subStepArray.indexOf(object)].time_aim"
                                        :class="(updateErrors !== null && updateErrors['subStepForm.'+subStepArray.indexOf(object)+'.time_aim']) ? 'c-select--error' : ''">
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
                        <div class="c-submit c-submit--flex">
                            <p class="c-button c-button--twoColumn" @click="addSubStep" v-if="!challengedFlg && subStepArray && Array(subStepArray) && subStepArray.length < 20">
                                サブSTEP追加
                            </p>
                            <p v-else>
                                <!--既にサブSTEPが20ある場合、サブSTEP追加のボタンは非表示-->
                            </p>

                            <button class="c-button c-button--orange c-button--twoColumn" >
                                更新する
                            </button>
                        </div>
                        <!--STEP更新時のバリデーションエラー表示部分-->
                        <div v-if="updateErrors" class="c-error p-editStep__error">
                            <ul v-for="array,idx in updateErrors" class="c-error__ul" :key="idx">
                                <li class="c-error__list" v-for="msg in array" :key="msg">
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
                step:[],
                stepForm: {
                    id:null,
                    title: '',
                    category_main: null,
                    category_sub: null,
                    content:'',
                    time_aim:0,
                    step_number:0,
                    image: null,
                    imageName: null,
                    subStepForm:[],
                    deletedSubStep:[],
                },
                countAddSubStep: 0,
                subStepArray: [],
                challengedFlg: false,
                selectedCategoryMain:null,
                sizeErrorMessage:'',
                preview:null,
                categoryList: mainCategoryJson["mainCategory"],
                categoryListSubSelected:[],

            }
        },
        methods: {
            //更新するSTEPをデータから取得する
            async fetchStep () {
                await this.$store.dispatch('step/fetchStep',{id: this.id, beforePath:'editStep'})

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
                    if(Array(this.step) && this.step['challenge_step'] && this.step['challenge_step'].length !== 0){
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
                    this.stepForm['imageName'] = (this.step['image_path']) ? this.step['image_path'] : null

                    this.preview = (this.step['image_path']) ?  '/storage/' + this.step['image_path'] : null

                    //取得したSTEPのサブSTEP数に応じて、サブSTEPを編集するための配列を動的に生成
                    if(Array(this.step) && this.step['substeps'] && this.step['substeps'].length !== 0){
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
                if(Array(this.stepForm) && this.stepForm['subStepForm']){
                    for(let i = 0; i<this.stepForm['subStepForm'].length; i++){
                    this.stepForm['subStepForm'][i]["order"] = i+1
                    this.stepForm['time_aim'] = Number(this.stepForm['time_aim']) + Number(this.stepForm['subStepForm'][i]["time_aim"])

                    //サブSTEP数を計算して追加する
                     this.stepForm['step_number'] = this.stepForm['subStepForm'].length
                    }
                }

                this.stepForm.category_main = (this.stepForm.category_main === 'メインカテゴリーを選択してください') ? null : this.stepForm.category_main
                this.stepForm.category_sub = (this.stepForm.category_sub === 'サブカテゴリーを選択してください') ? null : this.stepForm.category_sub

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
            //commonFuncのchangeSubCategoryを上書き
            //サブカテゴリーの表示項目を変更する
            changeSubCategory(categoryId){

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

                    this.stepForm['category_main'] = this.selectedCategoryMain;
                }
                this.categoryListSubSelected = (categoryId && typeof(categoryId) === 'number') ? this.categoryList[categoryId - 1]["subCategory"] : [];
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
                        //フォームでファイルが選択されたら実行される処理
            //ファイルのデータURLを取得する
            onFileChange (event) {
                //ファイル選択時エラーのメッセージを初期化
                this.sizeErrorMessage = ''

                // 何も選択されていなかったら処理中断
                if (event.target.files.length === 0) {
                    this.reset()
                    return false
                }

                // ファイルが画像ではなかったら処理を中断し、ファイル選択時のエラーを表示
                if (! event.target.files[0].type.match('image.*')) {
                    this.reset()
                    this.sizeErrorMessage = 'ファイル形式が違います。'
                    return false
                }
                // ファイルサイズが10MBを超えていたら処理を中断し、ファイル選択時のエラーを表示
                if ( event.target.files[0].size > 1024*1024*10) {
                    this.reset()
                    this.sizeErrorMessage = 'ファイルサイズが10MBを超えています。'
                    return false
                }

                // FileReaderクラスのインスタンスを生成
                const reader = new FileReader()

                // ファイルを読み込み終わったタイミングで実行する処理
                reader.onload = e => {
                    // previewに読み込み結果（データURL）を代入する
                    // previewに値が入ると<output>につけたv-ifがtrueと判定される
                    // また<output>内部の<img>のsrc属性はpreviewの値を参照しているので
                    // 結果として画像が表示される
                    this.preview = e.target.result
                }

                // ファイルを読み込む
                // 読み込まれたファイルはデータURL形式で受け取れる（上記onload参照）
                reader.readAsDataURL(event.target.files[0])
                //読み込まれたファイルのデータURLをupdateForm.myIconに代入する
                this.stepForm.image = event.target.files[0]
                this.stepForm.imageName = null
            },
            // ファイル選択欄の値とプレビュー表示をリセットする
            reset () {
                //DBにアイコンが登録されている場合はそのアイコンのpathを、そうでなければnullを格納
                this.preview = (this.$store.getters['step/stepDetailImagePath']) ? '/storage/'+this.$store.getters['step/stepDetailImagePath']:null

                this.$el.querySelector('input[type="file"]').value = null
                this.stepForm.image = null
                this.sizeErrorMessage = ''
            },
            // 登録されているアイコンを消去する
            deleteRegisterdImage() {
                this.stepForm.imageName = null;
                this.preview = null;
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
                handler: function(newNum, oldNum){
                //メインカテゴリーの変更が感知されたら、changeSubCategorを呼び出しサブカテゴリーを変更する
                this.changeSubCategory(newNum)
                },
                deep:true,
                immediate:true,
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
