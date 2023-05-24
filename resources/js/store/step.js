import {OK, CREATED, UNPROCESSABLE_ENTITY} from "../util"
import mainCategoryJson from "./../../json/categoryList.json"


//stateの定義
const state = {
    step: null,
    subStep: null,
    apiStatus: null,
    indexSteps: null,
    mySteps: null,
    myChallenge: null,
    currentPage: null,
    lastPage: null,
    myStepCurrentPage: null,
    myStepLastPage:null,
    myChallengeCurrentPage: null,
    myChallengeLastPage: null,
    stepDetail:null,
    registerErrorMessages: null,
    updateErrorMessages:null,
    indexSearchKeyword:'',
    indexSearchCategoryMain:'メインカテゴリーを選択してください',
    indexSearchCategorySub:'サブカテゴリーを選択してください',
    indexSearchSort:'normal',
    myStepSearchKeyword:'',
    myStepSearchCategoryMain:'メインカテゴリーを選択してください',
    myStepSearchCategorySub:'サブカテゴリーを選択してください',
    myStepSearchSort:'normal',
    myChallengeSearchKeyword:'',
    myChallengeSearchCategoryMain:'メインカテゴリーを選択してください',
    myChallengeSearchCategorySub:'サブカテゴリーを選択してください',
    myChallengeSearchSort:'normal',
    categoryList: mainCategoryJson['mainCategory']
}

//componentでstateを取得するためのgettersの定義
const getters = {
    index:  state => state.indexSteps ? state.indexSteps: null,
    mySteps: state => state.mySteps ? state.mySteps: null,
    myChallenge: state => state.myChallenge ? state.myChallenge: null,
    currentPage: state => state.currentPage ? state.currentPage: null,
    lastPage: state => state.lastPage ? state.lastPage: null,
    myStepCurrentPage: state => state.myStepCurrentPage ? state.myStepCurrentPage: null,
    myStepLastPage: state => state.myStepLastPage ? state.myStepLastPage: null,
    myChallengeCurrentPage: state => state.myChallengeCurrentPage ? state.myChallengeCurrentPage: null,
    myChallengeLastPage: state => state.myChallengeLastPage ? state.myChallengeLastPage: null,
    stepDetail: state => state.stepDetail ? state.stepDetail : null,
    stepDetailImagePath: state=> state.Detail ? state.stepDetail.image_path: null,
    indexSearchKeyword: state => state.indexSearchKeyword ? state.indexSearchKeyword : '',
    indexSearchCategoryMain: state => state.indexSearchCategoryMain ? state.indexSearchCategoryMain : 'メインカテゴリーを選択してください',
    indexSearchCategorySub: state => state.indexSearchCategorySub ? state.indexSearchCategorySub : 'サブカテゴリーを選択してください',
    indexSearchSort: state => state.indexSearchSort ? state.indexSearchSort : 'normal',
    myStepSearchKeyword: state => state.myStepSearchKeyword ? state.myStepSearchKeyword : '',
    myStepSearchCategoryMain: state => state.myStepSearchCategoryMain ? state.myStepSearchCategoryMain : 'メインカテゴリーを選択してください',
    myStepSearchCategorySub: state => state.myStepSearchCategorySub ? state.myStepSearchCategorySub : 'サブカテゴリーを選択してください',
    myStepSearchSort: state => state.myStepSearchSort ? state.myStepSearchSort : 'normal',
    myChallengeSearchKeyword: state => state.myChallengeSearchKeyword ? state.myChallengeSearchKeyword : '',
    myChallengeSearchCategoryMain: state => state.myChallengeSearchCategoryMain ? state.myChallengeSearchCategoryMain : 'メインカテゴリーを選択してください',
    myChallengeSearchCategorySub: state => state.myChallengeSearchCategorySub ? state.myChallengeSearchCategorySub : 'サブカテゴリーを選択してください',
    myChallengeSearchSort: state => state.myChallengeSearchSort ? state.myChallengeSearchSort : 'normal',

}

//stateを更新するためのmutaionsの定義
const mutations = {
    setStep (state, data) {
        state.step = data
    },
    setSubStep (state, data) {
        state.subStep = data
    },
    setIndexSteps (state, data) {
        state.indexSteps = data
    },
    setMySteps (state, data) {
        state.mySteps = data
    },
    setMyChallenge (state, data) {
        state.myChallenge = data
    },
    setCurrentPage (state, num) {
        state.currentPage = num
    },
    setLastPage (state, num) {
        state.lastPage = num
    },
    setMyStepCurrentPage (state, num) {
        state.currentPage = num
    },
    setMyStepLastPage (state, num) {
        state.lastPage = num
    },
    setMyChallengeCurrentPage (state, num) {
        state.currentPage = num
    },
    setMyChallengeLastPage (state, num) {
        state.lastPage = num
    },
    setStepDetail (state, data) {
        state.stepDetail = data
    },
    setApiStatus (state, status) {
        state.apiStatus = status
    },
    setIndexSearchConditions(state, data) {
        state.indexSearchKeyword = data.keyword
        state.indexSearchCategoryMain = data.selectedCategoryMain
        state.indexSearchCategorySub = data.selectedCategorySub
        state.indexSearchSort = data.sort
    },
    setMyStepSearchConditions(state, data) {
        state.myStepSearchKeyword = data.keyword
        state.myStepSearchCategoryMain = data.selectedCategoryMain
        state.myStepSearchCategorySub = data.selectedCategorySub
        state.myStepSearchSort = data.sort
    },
    setMyChallengeSearchConditions(state, data) {
        state.myChallengeSearchKeyword = data.keyword
        state.myChallengeSearchCategoryMain = data.selectedCategoryMain
        state.myChallengeSearchCategorySub = data.selectedCategorySub
        state.myChallengeSearchSort = data.sort
    },
    setRegisterErrorMessages (state, messages) {
        state.registerErrorMessages = messages
    },
    setUpdateErrorMessages (state, messages) {
        state.updateErrorMessages = messages
    }
}

//Vuexで実行するactionの定義
const actions = {

    //STEP一覧取得処理
    async index(context, data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //指定ページのSTEP一覧を取得し、返却されたデータをresponseに格納
        const response = await axios.get(`/api/steps?page=${data}`)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            if(!response.data.data || !Array(response.data.data)){
                return false
            }

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })


            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })


            //取得したSTEPにリレーションしているchallengesテーブルをもとに、
            //そのSTEPに挑戦している人数を計算する
            for(let i=0; i<response.data.data.length; i++){

                //リレーションしているchallengesテーブルのデータ（'challenge_step'）が0の場合
                if(response.data.data[i]['challenge_step'].length === 0){
                    //count_challengerに0を格納
                    response.data.data[i]['count_challenger'] = 0

                //リレーションしているchallengesテーブルのデータが0ではない場合
                }else{
                    const countChallenger = response.data.data[i]['challenge_step'].filter(function(object){
                        //challengesテーブルのデータが格納されている'challenge_step'では、
                        //challengeのデータとして、親元のSTEPのチャレンジデータ（substep_id = null）のものと、
                        //親元のSTEPに紐づいているサブSTEPのチャレンジデータ（substep_idに数字が入っている）がある
                        //そのため、挑戦者の人数をカウントするために、親元のSTEPのチャレンジデータ（substep_id = null）
                        //のみfilterし、そのデータ件数を'count_challenger'に格納する
                        if(object.substep_id === null){
                            return true
                        }
                    })
                    response.data.data[i]['count_challenger'] = countChallenger.length
                }
            }

            context.commit('setIndexSteps', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //STEP一覧から、指定条件でSTEPを検索する
    async search(context, data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //指定条件でSTEPを取得し、結果をresponseに格納
        const response = await axios.post(`/api/search?page=${data.page}`,data)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)
            //リロード・ページ遷移でも検索条件が保持されるように、検索条件をstateに格納しておく
            context.commit('setIndexSearchConditions', data)

            if(!response.data.data || !Array(response.data.data)){
                return false
            }

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })

            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })

            //取得したSTEPにリレーションしているchallengesテーブルをもとに、
            //そのSTEPに挑戦している人数を計算する
            for(let i=0; i<response.data.data.length; i++){

                //リレーションしているchallengesテーブルのデータ（'challenge_step'）が0の場合
                if(response.data.data[i]['challenge_step'].length === 0){
                    //count_challengerに0を格納
                    response.data.data[i]['count_challenger'] = 0

                //リレーションしているchallengesテーブルのデータが0ではない場合
                }else{
                    //challengesテーブルのデータが格納されている'challenge_step'では、
                    //challengeのデータとして、親元のSTEPのチャレンジデータ（substep_id = null）と、
                    //親元のSTEPに紐づいているサブSTEPのチャレンジデータ（substep_idに数字が入っている）がある
                    //そのため、挑戦者の人数をカウントするために、親元のSTEPのチャレンジデータ（substep_id = null）
                    //のみfilterし、そのデータ件数を'count_challenger'に格納する
                    const countChallenger = response.data.data[i]['challenge_step'].filter(function(object){
                        if(object.substep_id === null){
                            return true
                        }
                    })
                    response.data.data[i]['count_challenger'] = countChallenger.length
                }
            }
            context.commit('setIndexSteps', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //自分が登録したSTEP一覧取得処理
    async indexMySteps(context, data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //指定ページの自分が登録したSTEP一覧を取得し、返却されたデータをresponseに格納
        const response = await axios.get(`/api/mysteps?page=${data}`)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            if(!response.data.data || !Array(response.data.data)){
                return false
            }

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })

            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })

            //取得したSTEPにリレーションしているchallengesテーブルをもとに、
            //そのSTEPに挑戦している人数を計算する
            for(let i=0; i<response.data.data.length; i++){

                //リレーションしているchallengesテーブルのデータ（'challenge_step'）が0の場合
                if(response.data.data[i]['challenge_step'].length === 0){
                    //count_challengerに0を格納
                    response.data.data[i]['count_challenger'] = 0

                //リレーションしているchallengesテーブルのデータが0ではない場合
                }else{
                    //challengesテーブルのデータが格納されている'challenge_step'では、
                    //challengeのデータとして、親元のSTEPのチャレンジデータ（substep_id = null）と、
                    //親元のSTEPに紐づいているサブSTEPのチャレンジデータ（substep_idに数字が入っている）がある
                    //そのため、挑戦者の人数をカウントするために、親元のSTEPのチャレンジデータ（substep_id = null）
                    //のみfilterし、そのデータ件数を'count_challenger'に格納する
                    const countChallenger = response.data.data[i]['challenge_step'].filter(function(object){
                        if(object.substep_id === null){
                            return true
                        }
                    })
                    response.data.data[i]['count_challenger'] = countChallenger.length
                }
            }

            context.commit('setMySteps', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setMyStepCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setMyStepLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //検索条件の自分が登録したSTEP一覧取得処理
    async searchMySteps(context, data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //指定条件で自分が登録したSTEPを取得し、結果をresponseに格納
        const response = await axios.post(`/api/mySearch?page=${data.page}`,data)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)
            //リロード・ページ遷移でも検索条件が保持されるように、検索条件をstateに格納しておく
            context.commit('setMyStepSearchConditions', data)

            if(!response.data.data || !Array(response.data.data)){
                return false
            }

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })

            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })

            //取得したSTEPにリレーションしているchallengesテーブルをもとに、
            //そのSTEPに挑戦している人数を計算する
            for(let i=0; i<response.data.data.length; i++){
                //リレーションしているchallengesテーブルのデータ（'challenge_step'）が0の場合
                if(response.data.data[i]['challenge_step'].length === 0){
                    //count_challengerに0を格納
                    response.data.data[i]['count_challenger'] = 0

                //リレーションしているchallengesテーブルのデータが0ではない場合
                }else{
                    //challengesテーブルのデータが格納されている'challenge_step'では、
                    //challengeのデータとして、親元のSTEPのチャレンジデータ（substep_id = null）と、
                    //親元のSTEPに紐づいているサブSTEPのチャレンジデータ（substep_idに数字が入っている）がある
                    //そのため、挑戦者の人数をカウントするために、親元のSTEPのチャレンジデータ（substep_id = null）
                    //のみfilterし、そのデータ件数を'count_challenger'に格納する
                    const countChallenger = response.data.data[i]['challenge_step'].filter(function(object){
                        if(object.substep_id === null){
                            return true
                        }
                    })
                    response.data.data[i]['count_challenger'] = countChallenger.length
                }
            }
            context.commit('setMySteps', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //自分が挑戦したSTEP一覧取得処理
    async indexMyChallenge(context,data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //自分が挑戦したSTEPを取得し、結果をresponseに格納
        const response = await axios.get(`/api/myChallenge?page=${data}`)


        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })

            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })

            context.commit('setMyChallenge', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setMyChallengeCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setMyChallengeLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //検索条件の自分が挑戦したSTEP一覧取得処理
    async searchMyChallenge(context, data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //指定した検索条件の、自分が挑戦したSTEPを取得し、結果をresponseに格納
        const response = await axios.post(`/api/myChallengeSearch?page=${data.page}`,data)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)
            //リロード・ページ遷移でも検索条件が保持されるように、検索条件をstateに格納しておく
            context.commit('setMyChallengeSearchConditions', data)

            //一覧に表示させるための画像パスを取得
            response.data.data.forEach(step => {
                if(step["image_path"] === null){
                    step["image_path"] = "/images/category-image-" + step["category_main"] + ".jpg"
                }else{
                    step["image_path"] ="/storage/" + step["image_path"]
                }
            })

            //取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
            response.data.data.forEach(step => {
                step["category_main_id"] = step["category_main"]
                step["category_sub_id"] = step["category_sub"]
                step["category_sub"] = state.categoryList[step['category_main'] - 1]['subCategory'][step['category_sub'] - 1]['name']
                step["category_main"] = state.categoryList[step['category_main'] - 1]['name']
            })

            //取得したSTEPの作成日時をUTC形式からyyyy-mm-dd形式に変更する
            response.data.data.forEach(step => {
                const createDateUtc = step["created_at"]
                const newDate = new Date(createDateUtc)
                const createDateJst = newDate.getFullYear() + '-' + (newDate.getMonth()+1) + '-' +newDate.getDate()
                step["created_at"] = createDateJst
            })

            context.commit('setMyChallenge', response.data.data)
            //ページネーションの現在のページ数をstateに格納
            context.commit('setCurrentPage', response.data.current_page)
            //ページネーションの最後のページ数をstateに格納
            context.commit('setLastPage', response.data.last_page)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //STEP詳細取得処理
    async fetchStep(context, data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        console.log(data.beforePath)
        //指定idのSTEPのデータを一つ取得し、結果をresponseに格納
        const response = await axios.get(`/api/steps/${data.id}`)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            //STEP詳細画面からメソッドが呼び出された場合
            if(data.beforePath === 'detail'){

                //アイキャッチ画像のpathを取得
                if(response.data["image_path"] === null){
                    response.data["image_path"] = "/images/category-image-" + response.data["category_main"] + ".jpg"
                }else{
                    response.data["image_path"] ="/storage/" + response.data["image_path"]
                }

                //画面に表示するために、取得したSTEPのカテゴリー・サブカテゴリーを数字から日本語に変換する
                response.data["category_sub"] = state.categoryList[response.data['category_main'] - 1]['subCategory'][response.data['category_sub'] - 1]['name']
                response.data["category_main"] = state.categoryList[response.data['category_main'] - 1]['name']


            }
            if(!response.data['challenge_step'] || !Array(response.data['challenge_step'])){
                return false
            }


            //取得したSTEPにリレーションしているchallengesテーブルをもとに、
            //そのSTEPに挑戦している人数を計算する
            //リレーションしているchallengesテーブルのデータ（'challenge_step'）が0の場合
            if(response.data['challenge_step'].length === 0){
                //count_challengerに0を格納
                response.data['count_challenger'] = 0

            //リレーションしているchallengesテーブルのデータが0ではない場合
            }else{
                //challengesテーブルのデータが格納されている'challenge_step'では、
                //challengeのデータとして、親元のSTEPのチャレンジデータ（substep_id = null）と、
                //親元のSTEPに紐づいているサブSTEPのチャレンジデータ（substep_idに数字が入っている）がある
                //そのため、挑戦者の人数をカウントするために、親元のSTEPのチャレンジデータ（substep_id = null）
                //のみfilterし、そのデータ件数を'count_challenger'に格納する
                const countChallenger = response.data['challenge_step'].filter(function(object){
                    if(object.substep_id === null){
                        return true
                    }
                })
                response.data['count_challenger'] = countChallenger.length
            }

            context.commit('setStepDetail',response.data)

            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)
        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //STEP登録処理
    async create(context, data) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //ファイル情報をLaravelのcontrollerに送信するため、FormDataインスタンスを生成
        let formData = new FormData()

        //image以外をformDataインスタンスに格納
        formData.append('title',data.title)
        formData.append('category_main', data.category_main)
        formData.append('category_sub', data.category_sub)
        formData.append('content', data.content)

        //dataに、アイキャッチ画像の画像ファイルが含まれている場合
        if(data.image !== null){
            //画像ファイルを格納
            formData.append('image', data.image)
        }
        //HTTP通信のヘッダーにmultipart/form-dataを付与するconfigを定義
        let config = {headers:{
            'Content-Type' : 'multipart/form-data'
        }}

        //データをAPIに渡し、登録した結果をresponseに代入
        const response = await axios.post('/api/new', formData, config)


        if (response.status === CREATED) {
            //APIステータスが201CREATEDの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setStep', response.data)
            context.commit('setRegisterErrorMessages', null)

            context.commit('message/setContent', {
                content: 'STEPの概要を登録しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if (response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setRegisterErrorMessages', response.data.errors)
          }else{
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
            if(response.data.message){
                context.commit('message/setContent', {
                    content: response.data.message,
                    timeout: 5000
                },{root:true})
                context.commit('message/setDangerFlg', {
                    boolean : true,
                    timeout: 5000
                    },{root:true})
            }
        }
    },

    //サブSTEP登録処理
    async createSubStep(context,data) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //データをAPIに渡し、登録した結果をresponseに代入
        const response = await axios.post('/api/newSub',data)

        //1. サブSTEPの登録処理が成功した場合
        if (response.status === CREATED || response.status === OK) {
            //親元のSTEPのstep_number（サブSTEPの数）とtime（目安達成時間の合計）を更新する
            context.commit('setSubStep', response.data)

            if(!response.data || !Array(response.data)){
                return false
            }

            //登録したサブSTEPの個数を取得
            const subStepLength = response.data.length
            //登録したサブSTEPの目安達成時間を合計するための変数を用意
            let sumTime = 0

            for(let i=0;i<subStepLength;i++){
                //各サブSTEPのtime（目安達成時間）を合計する
                sumTime = sumTime + Number(response.data[i]['time_aim'])
            }
            //親元のSTEPのサブSTEP数・目安達成時間の合計を更新するための変数を用意
            const updateStepMainData = {
                time: sumTime,
                stepNumber : subStepLength,
                stepId: response.data[0]['step_id']
            }
            //APIにデータを渡し、更新処理を行う。その後、返却された結果をupdatedに格納
            const updated = await axios.post('/api/update',updateStepMainData)

            if(updated.status === OK){
                //APIステータスが200OKの場合、APIステータスにtrueを入れる
                context.commit('setApiStatus', true)

                context.commit('setStep', updated.data)

                context.commit('message/setContent', {
                    content: 'サブSTEPを登録しました！',
                    timeout: 5000
                    },{root:true})
                return false
            }
            ////APIステータスが200OK以外の場合（更新処理失敗）はAPIステータスにfalseを入れる
            context.commit('setApiStatus', false)

            if (updated.status === UNPROCESSABLE_ENTITY) {
                //ユーザー側で入力したデータを登録する訳ではないので、validationエラーは基本的にありえないが、
                //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
                context.commit('setRegisterErrorMessages', updated.data.errors)
            }else{
                //それ以外の場合はエラーコードをerror/setCodeに格納する
                //エラーコードの種類に応じて、component側でエラー画面を表示させる
                context.commit('error/setCode', updated.status, { root: true })
                if(response.data.message){
                    context.commit('message/setContent', {
                        content: response.data.message,
                        timeout: 5000
                      },{root:true})
                    context.commit('message/setDangerFlg', {
                        boolean : true,
                        timeout: 5000
                        },{root:true})
                }
            }
        }

        //2. サブSTEPの登録処理が失敗した場合
        //APIステータスが200OKもしくは201CREATED以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if (response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setRegisterErrorMessages', response.data.errors)
        }else{
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
            if(response.data.message){
                context.commit('message/setContent', {
                    content: response.data.message,
                    timeout: 5000
                  },{root:true})
                context.commit('message/setDangerFlg', {
                    boolean : true,
                    timeout: 5000
                    },{root:true})
            }
        }
    },

    //STEP更新処理
    async edit(context, data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //ファイル情報をLaravelのcontrollerに送信するため、FormDataインスタンスを生成
        let formData = new FormData()

        //emailとintroductionをformDataインスタンスに格納
        formData.append('id',data.id)
        formData.append('title',data.title)
        formData.append('category_main', data.category_main)
        formData.append('category_sub', data.category_sub)
        formData.append('content', data.content)
        formData.append('time_aim', data.time_aim)
        formData.append('step_number', data.step_number)




        //dataに、登録するアイコンの画像ファイルが含まれている場合
        if(data.image !== null){
            //画像ファイルを格納
            formData.append('image', data.image)
        }
        //imageName（DBで既にアイコンが登録されている場合の画像ファイル名）を変数に格納
        //DBにアイコンが登録されていない場合は、imageNameはnullで格納される
        formData.append('imageName', data.imageName)

        formData.append('subStepForm', JSON.stringify(data.subStepForm))
        formData.append('deletedSubStep', JSON.stringify(data.deletedSubStep))


        //HTTP通信のヘッダーにmultipart/form-dataを付与するconfigを定義
        let config = {headers:{
            'Content-Type' : 'multipart/form-data'
        }}


        //dataをAPIに渡し更新処理を行う。その後返却された結果をresponseに代入
        const response = await axios.post('/api/edit', formData, config)

        if (response.status === CREATED || response.status === OK) {
            //APIステータスが201CREATEDもしくは200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setStep', response.data)
            context.commit('message/setContent', {
                content: 'STEPを更新しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスがそれ以外（更新処理失敗）の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)


        if (response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setUpdateErrorMessages', response.data.errors)
        }else{
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
            if(response.data.message){
                context.commit('message/setContent', {
                    content: response.data.message,
                    timeout: 5000
                  },{root:true})
                context.commit('message/setDangerFlg', {
                    boolean : true,
                    timeout: 5000
                    },{root:true})
            }

        }
    },

    //STEP挑戦処理
    async challenge(context, data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //dataをAPIに私、challengesテーブルに新規挑戦データを作成。その後、返却された結果をresponseに代入
        const response = await axios.post('/api/challenge',data)

        if (response.status === CREATED || response.status === OK) {
            //APIステータスが201CREATEDもしくは200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setStep', response.data)
            context.commit('message/setContent', {
                content: '新しいSTEPへの挑戦を開始しました！',
                timeout: 5000
              },{root:true})

            return false
        }

        //APIステータスがそれ以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if (response.status === UNPROCESSABLE_ENTITY) {
            //ユーザー側で入力したデータを登録する訳ではないので、validationエラーは基本的にありえないが、
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setRegisterErrorMessages', response.data.errors)
        }else{
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
            if(response.data.message){
                context.commit('message/setContent', {
                    content: response.data.message,
                    timeout: 5000
                  },{root:true})
                context.commit('message/setDangerFlg', {
                    boolean : true,
                    timeout: 5000
                    },{root:true})
            }

        }
    },

    //登録されているSTEPを削除
    async delete(context, data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //渡ってきたdataをAPIに渡し、DBから指定されたSTEPを論理削除
        //その後、返却された結果をresponseに格納
        const response = await axios.post('/api/destroy',{id:data})

        if(response.status === OK){
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('message/setContent', {
                content: 'STEPを削除しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスがそれ以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)
        //エラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
        if(response.data.message){
            context.commit('message/setContent', {
                content: response.data.message,
                timeout: 5000
              },{root:true})
            context.commit('message/setDangerFlg', {
                boolean : true,
                timeout: 5000
                },{root:true})
        }
    },

    //indexの検索条件をリセット
    resetIndexSearchWord(context){
        const resetData = {
            keyword: '',
            selectedCategoryMain: 'メインカテゴリーを選択してください',
            selectedCategorySub: 'サブカテゴリーを選択してください',
            sort: "normal",
        }
        context.commit('setIndexSearchConditions', resetData)
    },

    //MySTEPの検索条件をリセット
    resetMyStepSearchWord(context){
        const resetData = {
            keyword: '',
            selectedCategoryMain: 'メインカテゴリーを選択してください',
            selectedCategorySub: 'サブカテゴリーを選択してください',
            sort: "normal",
        }
        context.commit('setMyStepSearchConditions', resetData)
    },

    //Myチャレンジの検索条件をリセット
    resetMyChallengeSearchWord(context){
        const resetData = {
            keyword: '',
            selectedCategoryMain: 'メインカテゴリーを選択してください',
            selectedCategorySub: 'サブカテゴリーを選択してください',
            sort: "normal",
        }
        context.commit('setMyChallengeSearchConditions', resetData)
    },
    //
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
