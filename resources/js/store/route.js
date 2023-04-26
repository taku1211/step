//stateの定義
const state = {
    prevRoute : null,
    prevPath : null,
}

//stateを更新するためのmutaionsの定義
const mutations = {
    setPrevRoute(state, url) {
        state.prevRoute = url
    },
    setPrevPath(state, path) {
        state.prevPath = path
    }
}

//componentでstateを取得するためのgettersの定義
const getters = {
    getPrevRoute : state => state.prevRoute ? state.prevRoute : null,
    getPrevPath : state => state.prevPath ? state.prevPath : null,
}

//Vuexで実行するactionの定義
const actions = {

    //遷移前のURLをstateに格納する処理
    setLocationUrl(context, url){
        context.commit('setPrevRoute',url)
    },
    //遷移前のpathをstateに格納する処理
    setLocationPath(context, path){
        context.commit('setPrevPath',path)
    }

}


export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
}
