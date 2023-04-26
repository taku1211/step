import {OK, CREATED, UNPROCESSABLE_ENTITY, UNAUTHORIZED} from "../util"

//stateの定義
const state = {
    user: null,
    apiStatus: null,
    loginErrorMessages: null,
    registerErrorMessages: null,
    updateErrorMessages: null,
    resetErrorMessages: null,
    updatePasswordErrorMessages: null,
}

//componentでstateを取得するためのgettersの定義
const getters = {
    check:  state => !! state.user,
    email: state => state.user ? state.user.email:'',
    id: state => state.user ? state.user.id:null,
    userId: state => state.user ? state.user.id: null,
    introduction: state => state.user ? state.user.introduction:null,
    icon: state => state.user ? state.user.icon:null,
}

//stateを更新するためのmutaionsの定義
const mutations = {
    setUser(state, user) {
        state.user = user
    },
    setApiStatus (state, status) {
        state.apiStatus = status
    },
    setLoginErrorMessages (state, messages) {
        state.loginErrorMessages = messages
    },
    setRegisterErrorMessages (state, messages) {
        state.registerErrorMessages = messages
    },
    setUpdateErrorMessages (state, messages) {
        state.updateErrorMessages = messages
    },
    setResetErrorMessages (state, messages) {
        state.resetErrorMessages = messages
    },
    setUpdatePasswordErrorMessages (state, messages) {
        state.updatePasswordErrorMessages = messages
    },
}

//Vuexで実行するactionの定義
const actions = {

    //ユーザー登録処理
    async register(context, data) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //登録するユーザーデータを変数に格納
        const registerData = {
            email: data.email,
            password: data.password,
            password_confirmation: data.password_confirmation
        }

        //ユーザーデータをAPIに渡し、結果をresponseに代入
        const response = await axios.post('/api/register', registerData)


        if (response.status === CREATED) {
            //APIステータスが201CREATEDの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setUser', response.data)

            context.commit('message/setContent', {
                content: '登録が完了しました！',
                timeout: 5000
              },{root:true})
            return false
          }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if (response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setRegisterErrorMessages', response.data.errors)
          } else {
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
          }
    },

    //ログイン処理
    async login(context, data) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //ログインするユーザー情報を変数に格納
        const loginData = {
            email: data.email,
            password: data.password,
        }

        //ログインするユーザー情報をAPIに渡し、結果をresponseに代入
        const response = await axios.post('/api/login', loginData)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setUser', response.data)

            context.commit('message/setContent', {
                content: 'ログインしました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if(response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setLoginErrorMessages', response.data.errors)
        } else {
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
        }
    },

    //ログアウト処理
    async logout(context) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //logout処理をaxiosで実行し、結果をresponseに格納する
        const response = await axios.post('/api/logout')

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            //ログアウトしたためsetUserのユーザー情報をnullにする
            context.commit('setUser', null)

            context.commit('message/setContent', {
                content: 'ログアウトしました！',
                timeout: 5000
              },{root:true})
            return false
          }

          //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
          context.commit('setApiStatus', false)
          //エラーコードの種類に応じて、component側でエラー画面を表示させる
          context.commit('error/setCode', response.status, { root: true })
    },

    //認証リセット処理
    async reset(context) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)
        //logout処理をaxiosで実行し、結果をresponseに格納する
        const response = await axios.post('/api/logout')

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            //ログアウトしたためsetUserのユーザー情報をnullにする
            context.commit('setUser', null)
          }

          //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
          context.commit('setApiStatus', false)
          //エラーコードの種類に応じて、component側でエラー画面を表示させる
          context.commit('error/setCode', response.status, { root: true })
    },

    //ユーザー情報更新処理
    async update(context, data){

        //APIステータスを空にする
        context.commit('setApiStatus', null)

        //ファイル情報をLaravelのcontrollerに送信するため、FormDataインスタンスを生成
        let formData = new FormData()

        //emailとintroductionをformDataインスタンスに格納
        formData.append('email',data.email)
        formData.append('introduction', data.introduction)

        //dataに、登録するアイコンの画像ファイルが含まれている場合
        if(data.myIcon !== null){
            //画像ファイルを格納
            formData.append('icon', data.myIcon)
        }
        //iconName（DBで既にアイコンが登録されている場合の画像ファイル名）を変数に格納
        //DBにアイコンが登録されていない場合は、iconNameはnullで格納される
        formData.append('iconName', data.iconName)

        //HTTP通信のヘッダーにmultipart/form-dataを付与するconfigを定義
        let config = {headers:{
            'Content-Type' : 'multipart/form-data'
        }}

        const response = await axios.post('/api/updateUser', formData, config )

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setUser', response.data)

            context.commit('message/setContent', {
                content: 'ユーザー情報を更新しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if(response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setUpdateErrorMessages', response.data.errors)
        } else {
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
        }
    },

    //パスワードリセットのメールを送信する処理
    async sendResetMail(context, data){
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        const response = await axios.post('/api/password/request', data)

        if (response.status === CREATED) {
            //APIステータスが201CREATEDの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('message/setContent', {
                content: 'パスワード再設定メールを送信しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if(response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setResetErrorMessages', response.data.errors)

        }else if(response.status === UNAUTHORIZED){
            //認証エラーの場合も、エラーメッセージを格納し、component側で表示させる
            context.commit('setResetErrorMessages', response.data.message)

        } else {
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
        }
    },

    //パスワードの再設定処理
    async resetPassword(context, data) {

        //APIステータスを空にする
        context.commit('setApiStatus', null)

        const response = await axios.post('/api/password/reset', data)

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('message/setContent', {
                content: 'パスワードを再設定しました！',
                timeout: 5000
              },{root:true})
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        if(response.status === UNPROCESSABLE_ENTITY) {
            //validationエラーの場合、エラーメッセージを格納し、component側で表示させる
            context.commit('setUpdatePasswordErrorMessages', response.data.errors)

        }else if(response.status === UNAUTHORIZED){
            //認証エラーの場合も、エラーメッセージを格納し、component側で表示させる
            context.commit('setUpdatePasswordErrorMessages', response.data.message)

        } else {
            //それ以外の場合はエラーコードをerror/setCodeに格納する
            //エラーコードの種類に応じて、component側でエラー画面を表示させる
            context.commit('error/setCode', response.status, { root: true })
        }

    },

    //認証チェック
    async currentUser(context) {
        //APIステータスを空にする
        context.commit('setApiStatus', null)

        const response = await axios.post('/api/user')

        const user = response.data || null

        if (response.status === OK) {
            //APIステータスが200OKの場合、APIステータスにtrueを入れる
            context.commit('setApiStatus', true)

            context.commit('setUser', user)
            return false
        }

        //APIステータスが200OK以外の場合はAPIステータスにfalseを入れる
        context.commit('setApiStatus', false)

        //それ以外の場合はエラーコードをerror/setCodeに格納する
        //エラーコードの種類に応じて、component側でエラー画面を表示させる
        context.commit('error/setCode', response.status, { root: true })
    }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
