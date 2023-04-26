import Vue from 'vue';
import VueRouter from 'vue-router';

//コンポーネントの読み込み
import LoginComponent from './components/LoginComponent.vue';
import RegisterComponent from './components/RegisterComponent.vue';
import TopPageComponent from './components/TopPageComponent.vue';
import StepIndexComponent from './components/StepIndexComponent.vue';
import MypageComponent from './components/MypageComponent.vue';
import SystemErrorComponent from './components/errors/SystemErrorComponent.vue';
import NotFoundErrorComponent from './components/errors/NotFoundErrorComponent.vue';
import SessionErrorComponent from './components/errors/SessionErrorComponent.vue';
import AddNewStepComponent from './components/AddNewStepComponent.vue';
import StepDetailComponent from './components/StepDetailComponent.vue';
import SubStepDetailComponent from './components/SubStepDetailComponent.vue';
import EditStepComponent from './components/EditStepComponent.vue';
import SettingComponent from './components/SettingComponent.vue';
import ForgotPasswordComponent from './components/ForgotPasswordComponent.vue';
import ResetPasswordComponent from './components/ResetPasswordComponent.vue';
import MyStepIndexComponent from './components/MyStepIndexComponent.vue';
import MyChallengeComponent from './components/MyChallengeComponent.vue';


//Vuexのインポート
import store from './store'


//vue-routerの使用宣言
Vue.use(VueRouter);


//パスとコンポーネントを紐づける
const routes = [
    {
        path:'/',
        name:'TopPageComponent',
        component:TopPageComponent,
    },
    {
        path: '*',
        name: 'NotFoundErrorComponent',
        component: NotFoundErrorComponent,
        beforeEnter(to, from, next){
                 next('/404')
        }
    },
    {
        path:'/login',
        name:'LoginComponent',
        component:LoginComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                //ログイン済みの場合はmypageへ遷移
                 next('/mypage')
            }else{
                 next()
            }
        }
    },
    {
        path:'/password/forgot',
        name:'ForgotPasswordComponent',
        component:ForgotPasswordComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                //ログイン済みの場合はmypageへ遷移
                 next('/mypage')
            }else{
                 next()
            }
        }
    },
    {
        path:'/password/reset',
        name:'ResetPasswordComponent',
        component:ResetPasswordComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                //ログイン済みの場合はmypageへ遷移
                 next('/mypage')
            }else{
                 next()
            }
        }
    },
    {
        path:'/register',
        name:'RegisterComponent',
        component:RegisterComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                //ログイン済みの場合はmypageへ遷移
                 next('/mypage')
            }else{
                 next()
            }
        }
    },
    {
        path:'/index',
        name:'StepIndexComponent',
        component:StepIndexComponent,
        props: route => {
            //遷移先のページが何ページ目かを取得
            const page = route.query.page
            //pageの値が整数かつ1以上かつページネーションの最終ページ以下であればその数字をpropsとして渡す
            //それ以外の場合は不正なページのため、1をpropsとして渡し、1ページ目に遷移させる
            return { page: (/^[1-9][0-9]*$/.test(page) && 1 <= page && page <= store.getters['step/lastPage']) ? page * 1 : 1 }
        },
    },
    {
        path:'/steps/:id',
        name:'StepDetailComponent',
        component:StepDetailComponent,
        props:true,
    },
    {
        path:'/substeps/:id',
        name:'SubStepDetailComponent',
        component:SubStepDetailComponent,
        props: true,
    },
    {
        path:'/edit/:id',
        name:'EditStepComponent',
        component:EditStepComponent,
        props: true,
    },
    {
        path:'/mypage',
        name:'MypageComponent',
        component:MypageComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                 next()
            }else{
                //ログインしていない場合は、ログインページへ遷移
                 next('/login')
            }
        }
    },
    {
        path:'/mypage/index',
        name:'MyStepIndexComponent',
        component:MyStepIndexComponent,
        props: route => {
            //遷移先のページが何ページ目かを取得
            const page = route.query.page
            //pageの値が整数かつ1以上かつページネーションの最終ページ以下であればその数字をpropsとして渡す
            //それ以外の場合は不正なページのため、1をpropsとして渡し、1ページ目に遷移させる
            return { page: (/^[1-9][0-9]*$/.test(page) && 1 <= page && page <= store.getters['step/lastPage']) ? page * 1 : 1 }
        },
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                 next()
            }else{
                //ログインしていない場合は、ログインページへ遷移
                 next('/login')
            }
        }
    },
    {
        path:'/mypage/challenge',
        name:'MyChallengeComponent',
        component:MyChallengeComponent,
        props: route => {
            //遷移先のページが何ページ目かを取得
            const page = route.query.page
            //pageの値が整数かつ1以上かつページネーションの最終ページ以下であればその数字をpropsとして渡す
            //それ以外の場合は不正なページのため、1をpropsとして渡し、1ページ目に遷移させる
            return { page: (/^[1-9][0-9]*$/.test(page) && 1 <= page && page <= store.getters['step/lastPage']) ? page * 1 : 1 }
        },
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                 next()
            }else{
                //ログインしていない場合は、ログインページへ遷移
                 next('/login')
            }
        }
    },
    {
        path:'/setting',
        name:'SettingComponent',
        component:SettingComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                 next()
            }else{
                //ログインしていない場合は、ログインページへ遷移
                 next('/login')
            }
        }
    },
    {
        path:'/new',
        name:'AddNewStepComponent',
        component:AddNewStepComponent,
        beforeEnter(to, from, next){
            //認証チェック
            if(store.getters['auth/check']){
                next()
            }else{
                //ログインしていない場合は、ログインページへ遷移
                next('/login')
            }
        }
    },
    {
        path:'/500',
        name: 'SystemErrorComponent',
        component: SystemErrorComponent
    },
    {
        path:'/419',
        name: 'SessionErrorComponent',
        component: SessionErrorComponent
    },
    {
        path: '/404',
        name: 'NotFoundErrorComponent2',
        component: NotFoundErrorComponent,
    },

]

//VueRouterインスタンスの作成
const router = new VueRouter({
    mode: 'history',
    routes,
    scrollBehavior () {
        return { x: 0, y: 0 }
    },
})

//app.jsでimportするために、VueRouterインスタンスをexportする
export default router;
