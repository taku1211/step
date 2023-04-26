//stateの定義
const state = {
    content: '',
}

//stateを更新するためのmutaionsの定義
const mutations = {
    setContent (state, { content, timeout }) {
        state.content = content

        //messageを表示させる時間（timeout）が指定されていない場合
        if (typeof timeout === 'undefined') {
            timeout = 3000
        }

        setTimeout(() => (state.content = ''), timeout)
},
}

export default {
    namespaced: true,
    state,
    mutations
}
