<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>結果</title>
    <style>
        table {
            table-layout: fixed;
            font-size: small;
        }

        th, td {
            border: solid 1px #999;
        }

        .elipsis {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
        }
    </style>
</head>
<body>
<table>
    <col style="width:28%;">
    <col style="width:20%;">
    <col style="width:3%;">
    <col style="width:20%;">
    <col style="width:3%;">
    <col style="width:28%;">
    <tr>
        <th>
            URL
        </th>
        <th>
            タイトル
        </th>
        <th>
            Status
        </th>
        <th>
            リダイレクト先タイトル
        </th>
        <th>
            Status
        </th>
        <th>
            リダイレクト先URL
        </th>
    </tr>
    <?php
    //タイムアウトしないようにする
    set_time_limit(0);

    //URL一覧をPOSTのデータから生成する
    $urls = [];
    if (isset($_POST['url_from'])) {
        $str = $_POST['url_from'];
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $urls = explode("\n", $str);
    }

    // 待ち時間の定義（整数秒）
    $interval = 3;

    // URLを順番に処理
    foreach ($urls as $url) {
        //処理ごとに指定秒数まつ
        sleep($interval);

        // 出力バッファの内容を送信する
        @ob_flush();
        @flush();

        // （1回目）cURLセッションを初期化
        $ch = curl_init($url);

        // （1回目）cURLオプションを設定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //FOLLOWLOCATION すると 最終的なリダイレクト結果まで一気に行くのでそれはしない
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // （1回目）HTTPリクエストを実行
        $response = curl_exec($ch);

        // （1回目）ステータスコードを取得
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // （1回目）cURLセッションを終了
        curl_close($ch);

        // （1回目）ページタイトルを取得
        $dom = new DOMDocument();
        @$dom->loadHTML($response);
        $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;


        // 1回目のステータスコードがリダイレクト系の時だけ、最終リダイレクト先の確認処理
        if ($httpCode !== 301 && $httpCode !== 302 && $httpCode !== 303 && $httpCode !== 307) {
            $titleR = "-";
            $httpCodeR = "-";
            $redirectUrlR = "-";
        }else{

            // （2回目）cURLセッションを初期化
            $chR = curl_init($url);

            // （2回目）cURLオプションを設定
            curl_setopt($chR, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chR, CURLOPT_FOLLOWLOCATION, true);

            // （2回目）HTTPリクエストを実行
            $responseR = curl_exec($chR);

            // （2回目）ステータスコードを取得
            $httpCodeR = curl_getinfo($chR, CURLINFO_HTTP_CODE);

            // （2回目）リダイレクト先のURLを取得
            $redirectUrlR = curl_getinfo($chR, CURLINFO_EFFECTIVE_URL);

            // （2回目）cURLセッションを終了
            curl_close($chR);

            // （2回目）ページタイトルを取得
            $domR = new DOMDocument();
            @$domR->loadHTML($responseR);
            $titleR = $domR->getElementsByTagName('title')->item(0)->nodeValue;

        }

        ?>
        <tr>
            <td>
                <?php echo $url ?>
            </td>
            <td style="max-width: 0;">
                <div class="elipsis">
                    <?php echo $title ?>
                </div>
            </td>
            <td>
                <?php echo $httpCode ?>
            </td>
            <td style="max-width: 0;">
                <div class="elipsis">
                    <?php echo $titleR ?>
                </div>
            </td>
            <td>
                <?php echo $httpCodeR ?>
            </td>
            <td>
                <?php echo $redirectUrlR ?>
            </td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
