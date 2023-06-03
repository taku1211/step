<template>
    <div id="l-main--siteWidth">
        <!--新規STEP登録画面-->
        <div>

                <h2 class="c-ornament">
                    <span class="c-ornament__border">
                        1. STEP概要の登録
                    </span>
                </h2>

                <!--親元のSTEPを登録するフォーム-->
                <form action="" class="c-form p-newStepForm" @submit.prevent="createStep">
                        <!--STEPタイトル入力部分-->
                        <label for="title" class="c-label c-label--marginl">
                            タイトル
                        </label>
                        <input id="title" type="text" name="title" class="c-input" v-bind:disabled="createSubStepFlg"
                                placeholder="タイトルを入力" v-model="stepForm.title"
                                :class="[createMainStepFlg ? '':'c-input--invalid',(registerErrors !== null && registerErrors.title) ? 'c-input--error' : '']">
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error">
                            <ul v-if="registerErrors.title" class="c-error__ul">
                                <li class="c-error__list" v-for="msg in registerErrors.title" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--メインカテゴリー選択部分-->
                        <label for="categoryMain" class="c-label c-label--marginl">
                            メインカテゴリー
                        </label>
                        <select name="categoryMain" id="categoryMain" class="c-select c-select--boxshadow c-select--fullWidth" v-model="selectedCategoryMain" v-bind:disabled="createSubStepFlg"
                                :class="[createMainStepFlg ? '':'c-select--invalid',(registerErrors !== null && registerErrors.category_main) ? 'c-select--error' : '']">
                            <option value="メインカテゴリーを選択してください" class="c-select__option c-select__option--disable" disabled>
                                メインカテゴリーを選択
                            </option>
                            <option :value="category.id" class="c-select__option" v-for="category in categoryList"
                                    :key="category.id">
                                    {{ category.name }}
                            </option>
                        </select>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error">
                            <ul v-if="registerErrors.category_main" class="c-error__ul">
                                <li class="c-error__list" v-for="msg in registerErrors.category_main" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--サブカテゴリー選択部分-->
                        <label for="categorySub" class="c-label c-label--marginl">
                            サブカテゴリー
                        </label>
                        <select name="categorySub" id="categorySub" class="c-select c-select--boxshadow c-select--fullWidth" v-model="stepForm.category_sub" v-bind:disabled="createSubStepFlg"
                                :class="[createMainStepFlg ? '':'c-select--invalid',(registerErrors !== null && registerErrors.category_sub) ? 'c-select--error' : '']">
                            <option value="サブカテゴリーを選択してください" class="c-select__option c-select__option--disable" disabled>
                                サブカテゴリーを選択
                            </option>
                            <option :value="category.id" class="c-select__option" v-for="category in categoryListSubSelected"
                                    :key="category.id">
                                    {{ category.name }}
                            </option>
                        </select>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error">
                            <ul v-if="registerErrors.category_sub" class="c-error__ul">
                                <li class="c-error__list" v-for="msg in registerErrors.category_sub" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!--STEP紹介文入力部分-->
                        <label for="content" class="c-label c-label--marginl">
                            STEP紹介文※500字以内
                        </label>
                        <textarea name="content" id="content" cols="30" rows="10" class="c-textarea"
                                  v-model="stepForm.content" v-bind:disabled="createSubStepFlg" placeholder="500文字以内の紹介文を入力できます。"
                                  :class="[createMainStepFlg ? '':'c-textarea--invalid',(registerErrors !== null && registerErrors.content) ? 'c-textarea--error' : '']">
                        </textarea>
                        <!--バリデーションエラー表示部分-->
                        <div v-if="registerErrors" class="c-error">
                            <ul v-if="registerErrors.content" class="c-error__ul">
                                <li class="c-error__list" v-for="msg in registerErrors.content" :key="msg">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    {{ msg }}
                                </li>
                            </ul>
                        </div>

                        <!-- STEPアイキャッチ画像登録部分 -->
                        <label for="image" class="c-label c-label--marginl">
                            STEPアイキャッチ画像※10MB以内<br>
                            <span class="u-fontSizeSmall">※画像を登録しない場合は、カテゴリー毎にデフォルトのアイキャッチ画像を表示します。</span>
                        </label>
                        <input id="image" type="file" name="image" class="c-input" v-bind:disabled="createSubStepFlg"
                                placeholder="タイトルを入力してください" @change="onFileChange"
                                :class="[createMainStepFlg ? '':'c-input--invalid',(registerErrors !== null && registerErrors.image) ? 'c-input--error' : '']">

                        <!--バリデーションエラー部分-->
                        <div v-if="registerErrors" class="c-error">
                                <ul v-if="registerErrors.image" class="c-error__ul">
                                    <li class="c-error__list" v-for="msg in registerErrors.image" :key="msg">
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
                        <!--アイキャッチプレビュー部分-->
                        <div v-if="preview">
                            <label class="c-label c-label--marginl u-pointerNone">
                                アイキャッチ画像プレビュー
                            </label>
                            <output>
                                <img :src="preview" alt="アップロード画像" class="c-imagePreview">
                            </output>
                        </div>

                        <!--親元のSTEPを登録するボタン-->
                        <div class="c-submit">
                            <button class="c-button c-button--orange"
                            :class="createMainStepFlg ? '':'c-button--invalid' " v-bind:disabled="createSubStepFlg">
                                STEP概要登録
                            </button>
                        </div>
                </form>

                <!--親元のSTEPに紐づくサブSTEPを登録する部分-->
                <transitionGroup name="c-fadeSoon" tag="div">
                    <h2 class="c-ornament" v-if="createSubStepFlg" key="c-ornament">
                        <span class="c-ornament__border">
                            2. サブSTEPの登録
                        </span>
                    </h2>

                    <form key="p-newStep__form" class="c-form p-newStep__form" v-if="createSubStepFlg" @submit.prevent="createSubStep">
                            <!--一つずつのサブSTEP登録パネル部分-->
                            <transitionGroup name="c-fade" tag="div">
                                <div class="c-subStep" v-for="object in subStepArray" :key="object">
                                    <!--サブSTEP登録パネルヘッダー-->
                                    <div class="c-subStep__head">
                                        <p class="c-subStep__icon c-subStep__icon--disable">✕</p>
                                        <h3 class="c-subStep__title">STEP {{ subStepArray.indexOf(object)+1 }}</h3>
                                        <p class="c-subStep__icon" @click="removeSubStep(subStepArray.indexOf(object))">✕</p>
                                    </div>
                                    <!--サブSTEPタイトル入力部分-->
                                    <label :for="'subTitle'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                        タイトル
                                    </label>
                                    <input :id="'subTitle'+(subStepArray.indexOf(object)+1)" type="text" :name="'subTitle'+(subStepArray.indexOf(object)+1)"
                                           class="c-input c-input--marginBottomL" v-model="subStepForm[subStepArray.indexOf(object)].subTitle"
                                           placeholder="タイトルを入力" :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subTitle']) ? 'c-input--error' : ''">
                                    <!--サブSTEP内容入力部分-->
                                    <label :for="'subContent'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                        内容※500字以内
                                    </label>
                                    <textarea :name="'subContent'+(subStepArray.indexOf(object)+1)" :id="'subContent'+(subStepArray.indexOf(object)+1)"
                                              cols="20" rows="10" class="c-textarea c-textarea--heightLow" v-model="subStepForm[subStepArray.indexOf(object)].subContent"
                                              :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subContent']) ? 'c-textarea--error' : ''"
                                              placeholder="500文字以内で入力できます。">
                                    </textarea>
                                    <!--サブSTEP目安達成時間選択部分-->
                                    <label :for="'subTime'+(subStepArray.indexOf(object)+1)" class="c-label c-label--marginl">
                                        目安達成時間
                                    </label>
                                    <select :name="'subTime'+(subStepArray.indexOf(object)+1)" :id="'subTime'+(subStepArray.indexOf(object)+1)"
                                            class="c-select c-select--boxshadow c-select--fullWidth" v-model="subStepForm[subStepArray.indexOf(object)].subTime"
                                            :class="(registerErrors !== null && registerErrors[subStepArray.indexOf(object)+'.subTime']) ? 'c-select--error' : ''">
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
                            <div class="c-submit c-submit--flex" key="c-submit">
                                <p class="c-button c-button--twoColumn" @click="addSubStep" v-if="subStepArray && Array(subStepArray) && subStepArray.length < 20">
                                    サブSTEP追加
                                </p>
                                <p v-else>
                                    <!--既にサブSTEPが20ある場合、サブSTEP追加のボタンは非表示-->
                                </p>

                                <button class="c-button c-button--orange c-button--twoColumn" v-if="subStepArray && Array(subStepArray) && subStepArray.length > 0">
                                    STEP登録
                                </button>
                            </div>

                            <!--サブSTEPのバリデーションエラー表示部分-->
                            <div v-if="registerErrors" class="c-error">
                                <ul v-for="array,idx in registerErrors" class="c-error__ul" :key="idx">
                                    <li class="c-error__list" v-for="msg in array" :key="msg">
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
    import CategoryListJson from "./../../json/categoryList.json"
    export default {
        data: function() {
            return {
                stepForm: {
                    title: '',
                    category_main: null,
                    category_sub:null,
                    content:'',
                    image: null,
                    imageName: null,
                },
                selectedCategoryMain: 'メインカテゴリーを選択してください',
                subStepForm: [{subTitle:'',subContent:'', subTime: null, order:null}],
                createMainStepFlg:true,
                createSubStepFlg:false,
                countAddSubStep: 1,
                subStepArray: [1],
                sizeErrorMessage:'',
                preview:'',
                categoryList: CategoryListJson["mainCategory"],
                categoryListSubSelected:[],
            }
        },
        methods: {
            //新しいSTEP（親元）を登録する
            async createStep() {
                //v-modelで紐づいていないメインカテゴリーのみselectedCategoryMainの値を代入する
                if(this.selectedCategoryMain !== 'メインカテゴリーを選択してください'){
                    this.stepForm.category_main = this.selectedCategoryMain
                }
                this.stepForm.category_main = (this.stepForm.category_main === 'メインカテゴリーを選択してください') ? null : this.stepForm.category_main
                this.stepForm.category_sub = (this.stepForm.category_sub === 'サブカテゴリーを選択してください') ? null : this.stepForm.category_sub
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
            //commonFuncのchangeSubCategoryを上書き
            //サブカテゴリーの表示項目を変更する
            changeSubCategory(categoryId){
                //既に選択されているサブカテゴリーを空にする
                this.stepForm.category_sub = null
                this.categoryListSubSelected = (categoryId && typeof(categoryId) === 'number') ? this.categoryList[categoryId - 1]["subCategory"] : [];
            },
            //サブSTEPを登録する
            async createSubStep(){
                if(this.subStepForm && Array(this.subStepForm)){
                    //サブSTEPの順番（order）をsubStepForm配列に追加する
                    for(let i = 0; i<this.subStepForm.length; i++){
                        this.subStepForm[i]["order"] = i+1
                    }
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
                //読み込まれたファイルのデータURLをstepForm.imageに代入する
                this.stepForm.image = event.target.files[0]
            },
            // ファイル選択欄の値とプレビュー表示をリセットする
            reset () {
                this.preview = null

                this.$el.querySelector('input[type="file"]').value = null
                this.stepForm.image = null
                this.sizeErrorMessage = ''
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
                //immediate:true,
            }
        },
    }

</script>
