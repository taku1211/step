
//stateの定義
//state.codeに419（41エラー）、500（500エラー）などの数字が格納され、
//その数字をcomponent側で取得することでエラーページに遷移させている

const state = {
    code: null
}

//stateを更新するためのmutaionsの定義
const mutations = {
    setCode (state, code) {
        state.code = code
    }
}

export default {
    namespaced: true,
    state,
    mutations
}
