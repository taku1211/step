import Vue from 'vue'
import Vuex from 'vuex'
//Vuexのstateの値を保持するためのプラグインを読み込み
import createPersistedState from 'vuex-persistedstate'

//各storeの読み込み
import auth from './auth'
import error from './error'
import step from './step'
import message from './message'
import route from './route'



//Vuexの使用宣言
Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    auth,
    error,
    step,
    message,
    route,
  },
  //Vuexのstateの値を保持するためのプラグイン
  plugins: [createPersistedState(
    {
      key: 'StepAppVuex',
      //保持するstateを指定
      paths:[
          'step.currentPage',
          'step.lastPage',
          'route.prevRoute',
          'route.prevPath',
          'step.indexSearchKeyword',
          'step.indexSearchCategoryMain',
          'step.indexSearchCategorySub',
          'step.indexSearchSort',
          'step.myStepSearchKeyword',
          'step.myStepSearchCategoryMain',
          'step.myStepSearchCategorySub',
          'step.myStepSearchSort',
      ],
      storage:window.sessionStrage
    }
)],
})

export default store
