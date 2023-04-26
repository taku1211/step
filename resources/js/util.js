//=================================================
//クッキーの取得処理
//=================================================

//LaravelではフォームにCSRFトークンを含める
//しかし、Laravel×Vue.jsのSPAではフォーム（inputタグ）に@csrfを設置できない
//そのため、クッキーからトークンを取りだし、HTTPヘッダーにそのトークンを含めて送信させ、
//CSRfチェックが行われるようにする

/**
 * クッキーの値を取得する
 * @param {String} searchKey 検索するキー
 * @returns {String} キーに対応する値
 */

export function getCookieValue (searchKey) {
    if (typeof searchKey === 'undefined') {
      return ''
    }

    let val = ''

    document.cookie.split(';').forEach(cookie => {
      const [key, value] = cookie.split('=')
      if (key === searchKey) {
        return val = value
      }
    })
    return val
  }

//resources/js/bootstrap.jsで、取得したgetCookieValueをHTTPヘッダーに付与する


//=================================================
//HTTPリクエストのレスポンスコードの定義
//=================================================

export const OK = 200
export const CREATED = 201
export const INTERNAL_SERVER_ERROR = 500
export const UNPROCESSABLE_ENTITY = 422
export const unknown_status = 419
export const NOT_FOUND = 404
export const UNAUTHORIZED = 401
export const REDIRECT = 302
