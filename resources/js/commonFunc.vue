<script>
    export default{
        //コンポーネント間で共通しているメソッドを定義
        methods: {
            //Vuexに表示ページのURL情報を保存する
            storeUrlData(){
                //この処理を行うことで、STEP詳細からこのページへ戻る際に、戻るページのpathを特定している
                this.$store.dispatch('route/setLocationUrl',(location.pathname+location.search))
                this.$store.dispatch('route/setLocationPath',(location.pathname))
            },

            //ページネーションのための現在のページ数・最終ページを取得
            getPageData(){
                this.currentPage = this.$store.getters['step/currentPage']
                this.lastPage = this.$store.getters['step/lastPage']
            },

            //サブカテゴリーの表示項目を変更する
            changeSubCategory(categoryId){
                this.searchForm.selectedCategorySub = this.categorySubSearch
                this.categoryListSubSelected = (categoryId && typeof(categoryId) === 'number') ? this.categoryList[categoryId - 1]["subCategory"] : [];
            },

            //URLの「?page=」以降に直接、不正な値が入力された場合の対策
            //不正な値が入力された場合は、404NotFoundページへ遷移させる
            checkInvalidPageNum(){
                //表示ページのsearch情報を取得
                const search = location.search
                    //routingで管理しているページ情報を取得
                    const page = '?page=' + this.page

                    if(search !== '' && search !== page  ){
                        //一致しない場合（不正な値など）は、404notFoundエラーページへ遷移
                        this.$router.push('*')
                    }
            },

            //検索処理を行う前の共通処理
            prepareBeforeSearch(){
                //検索ボタンを押して検索した場合は、必ず1ページ目を表示するようにする
                this.searchForm.page = 1
                this.searchFlg = true

                if(this.searchForm.selectedCategoryMain === this.categoryMainSearch || this.searchForm.selectedCategoryMain === this.categoryNoSelect){
                    this.searchForm.selectedCategoryMain = ''
                }

                if(this.searchForm.selectedCategorySub === this.categorySubSearch || this.searchForm.selectedCategorySub === this.categoryNoSelect){
                    this.searchForm.selectedCategorySub = ''
                }
            },

            //検索状態でページネーションを行う前の共通処理
            prepareSearchedPagination(){
                //検索ボタンを押して検索した場合と異なり、
                //searchForm.pageを1にせず、2ページ目以降に進めるようにする

                if(this.searchForm.selectedCategoryMain === this.categoryMainSearch || this.searchForm.selectedCategoryMain === this.categoryNoSelect){
                    this.searchForm.selectedCategoryMain = ''
                }

                if(this.searchForm.selectedCategorySub === this.categorySubSearch || this.searchForm.selectedCategorySub === this.categoryNoSelect){
                    this.searchForm.selectedCategorySub = ''
                }
            },

            //inputのtype（パスワード入力部分・パスワード再入力部分）を変更する
            changeInputType(){
                if(this.inputType === 'password'){
                    this.inputType = 'text'
                    this.eyeStyle = 'fa-sharp fa-solid fa-eye-slash'
                }else{
                    this.inputType = 'password'
                    this.eyeStyle = 'fa-solid fa-eye'
                }
            },

            //ログアウト処理
            async logout() {
                await this.$store.dispatch('auth/logout')

                //apiStatusがtrue（200OK）であれば
                if (this.apiStatus) {
                    //ログインページへ遷移する
                     this.$router.push('/login')
                }
            },

            //ページ上部へ戻る
            returnTop(){
                window.scroll({
                    top:0,
                    behavior:'smooth',
                })
            },

        }
    }
</script>
