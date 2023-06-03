<template>
    <div id="l-main--siteWidth">
        <!--ユーザー情報更新画面-->
        <section class="p-setting">
            <h2 class="c-ornament">
                <span class="c-ornament__border">
                    ユーザー情報更新
                </span>
            </h2>
            <!--ユーザー情報更新フォーム-->
            <form class="c-form" @submit.prevent="update">
                <!--メールアドレス入力部分-->
                <label for="email" class="c-label c-label--marginl">
                    メールアドレス
                </label>
                <input id="email" type="text" name="email" class="c-input"
                       v-model="updateForm.email" placeholder="step@example.com"
                       :class="(updateErrors !== null && updateErrors.email) ? 'c-input--error' : ''">
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error">
                    <ul v-if="updateErrors.email" class="c-error__ul">
                        <li class="c-error__list"  v-for="msg in updateErrors.email" :key="msg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            {{ msg }}
                        </li>
                    </ul>
                </div>
                <!--自己紹介入力部分-->
                <label for="introduction" class="c-label c-label--marginl">
                    自己紹介文 ※500文字以内
                </label>
                <textarea name="introduction" id="introduction" cols="30" rows="10" class="c-textarea"
                          v-model="updateForm.introduction" placeholder="500文字以内の自己紹介文を入力できます。"
                          :class="(updateErrors !== null && updateErrors.introduction) ? 'c-textarea--error' : ''">
                </textarea>
                <!--バリデーションエラー表示部分-->
                <div v-if="updateErrors" class="c-error">
                        <ul v-if="updateErrors.introduction" class="c-error__ul">
                            <li class="c-error__list" v-for="msg in updateErrors.introduction" :key="msg">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                {{ msg }}
                            </li>
                        </ul>
                </div>
                <!--ユーザーアイコン登録・更新部分-->
                <label for="icon" class="c-label c-label--marginl">
                    ユーザーアイコン
                </label>
                <input id="icon" type="file" name="icon" class="c-input" @change="onFileChange">
                <!--バリデーションエラー部分-->
                <div v-if="updateErrors" class="c-error p-setting__error">
                        <ul v-if="updateErrors.icon" class="c-error__ul">
                            <li class="c-error__list" v-for="msg in updateErrors.icon" :key="msg">
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
                <!--登録済みのアイコンをリセットする部分-->
                <div class="p-setting__deleteMyIcon">
                    <p class="c-button p-setting__button" @click="deleteRegisterdIcon" v-if="updateForm.iconName">
                        アイコン初期化
                    </p>
                </div>
                <!--アイコンプレビュー部分-->
                <div class="p-setting_preview" v-if="preview">
                    <label class="c-label c-label--marginl u-pointerNone">
                        アイコンプレビュー
                    </label>
                    <output class="p-setting__myIcon">
                        <img :src="preview" alt="アップロード画像" class="p-setting__img">
                    </output>
                </div>
                <!--ユーザー情報更新ボタン部分-->
                <div class="c-submit">
                    <button type="submit" class="c-button c-button--orange p-setting__button">更新する</button>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
    import imageCompression from "browser-image-compression";

    export default {
        data: function(){
            return {
                updateForm : {
                    email : this.$store.getters['auth/email'],
                    introduction:this.$store.getters['auth/introduction'],
                    myIcon: null,
                    iconName:(this.$store.getters['auth/icon']) ?this.$store.getters['auth/icon']:null,
                },
                sizeErrorMessage:'',
                preview: (this.$store.getters['auth/icon']) ? '/storage/'+this.$store.getters['auth/icon']:null,
                imageOption: {
                    maxSizeMB: 0.5,
                    maxWidthOrHeight: 800
                }
            }
        },
        methods: {
            //ユーザー情報更新処理
            async update(){

                await this.$store.dispatch('auth/update', this.updateForm)

                //apiStatusがtrue（更新成功）であれば、
                if(this.apiStatus) {
                    //マイページへ遷移する
                    this.$router.push('/mypage')
                }
            },
            //バリデーションエラーを空にする
            clearError () {
                     this.$store.commit('auth/setUpdateErrorMessages', null)
            },
            //フォームでファイルが選択されたら実行される処理
            //ファイルのデータURLを取得する
            async onFileChange (event) {
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

                try {
                // 表示を軽くするため圧縮画像を生成する
                this.updateForm.myIcon =  await imageCompression(event.target.files[0], this.imageOption);
                } catch (error) {
                    //圧縮ができなかった場合は、元の画像をそのまま代入する
                    this.updateForm.myIcon = event.target.files[0]
                }
                this.updateForm.iconName = null

            },
            // ファイル選択欄の値とプレビュー表示をリセットする
            reset () {
                //DBにアイコンが登録されている場合はそのアイコンのpathを、そうでなければnullを格納
                this.preview = (this.$store.getters['auth/icon']) ? '/storage/'+this.$store.getters['auth/icon']:null

                this.$el.querySelector('input[type="file"]').value = null
                this.updateForm.myIcon = null
                this.iconSize = null
                this.sizeErrorMessage = ''
            },
            // 登録されているアイコンを消去する
            deleteRegisterdIcon() {
                this.updateForm.iconName = null;
                this.preview = null;
            },
        },
        computed: {
            //apiStatusステートを参照する
            apiStatus () {
                return this.$store.state.auth.apiStatus
            },
            //updateErrorMessagesステートを参照する
            updateErrors () {
                 return this.$store.state.auth.updateErrorMessages
            }
        },
        created () {
            //create1時にバリデーションエラーを初期化する
             this.clearError()
        },

    }

</script>
