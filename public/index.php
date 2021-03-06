<?php
require __DIR__ . '/../vendor/autoload.php';
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
 
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = ""; //input your channel access token that was get from Line developer > messaging API
$channel_secret = ""; //input your channel secret that was get from Line developer > basic settings
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});

// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    


    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    if (strtolower($event['message']['text']) == 'menu') {
 
                        $flexTemplate = file_get_contents("../flex_message.json"); // template flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);
 
                    } else if (strtolower($event['message']['text']) == 'cart') {
                        
                        include ('db.php');

                        $sql = 'SELECT product_name, product_price from cart';
                         
                        $q = $pdo->query($sql);
                        $q->setFetchMode(PDO::FETCH_ASSOC);

                        $details = "Cart : \n";

                        while ($r = $q->fetch()){
                            $details .= "name = ".$r['product_name'].", price = ".$r['product_price']."\n";
                            $sum += (int)$r['product_price'];
                        }

                        $details .= "total :".$sum;

                        /*$result = $bot->replyText($event['replyToken'], $details);*/

                        $textMessageBuilder1 = new TextMessageBuilder($details);
                        $textMessageBuilder2 = new TextMessageBuilder('ketik keyword "buy" untuk melakukan pembelian, dan "cancel" untuk mengcancel pemesanan');

                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($textMessageBuilder1);
                        $multiMessageBuilder->add($textMessageBuilder2);

                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    } else if (strtolower($event['message']['text']) == 'buy') {

                        include ('db.php');

                        $sql = "DELETE FROM cart WHERE id = 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        $result = $bot->replyText($event['replyToken'], "anda telah melakukan pembayaran silahkan transfer ke no rekening berikut xxxxxxxxxxxxxxxx");

                    } else if (strtolower($event['message']['text']) == 'cancel') {

                        include ('db.php');

                        $sql = "DELETE FROM cart WHERE id = 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();

                        $result = $bot->replyText($event['replyToken'], "anda telah mencancel pemesanan anda");

                    }else if (strtolower($event['message']['text']) == 'buy tiramisu') {

                            include ('db.php');

                            $sql = "INSERT INTO cart (id, product_name, product_price) Values (1,'tiramisu',30000)";
                            $pdo->exec($sql);


                        $textMessageBuilder1 = new TextMessageBuilder("added to cart");
                        $textMessageBuilder2 = new TextMessageBuilder('ketik keyword "cart" untuk melihat pemesanan anda');

                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($textMessageBuilder1);
                        $multiMessageBuilder->add($textMessageBuilder2);

                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                        /*$result = $bot->replyText($event['replyToken'], "added to cart");*/

                    }else if (strtolower($event['message']['text']) == 'buy cheesecake') {

                            include ('db.php');

                            $sql = "INSERT INTO cart (id, product_name, product_price) Values (1,'cheesecake',35000)";
                            $pdo->exec($sql);

                        
                        $textMessageBuilder1 = new TextMessageBuilder("added to cart");
                        $textMessageBuilder2 = new TextMessageBuilder('ketik keyword "cart" untuk melihat pemesanan anda');

                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($textMessageBuilder1);
                        $multiMessageBuilder->add($textMessageBuilder2);

                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    }else if (strtolower($event['message']['text']) == 'buy brownies') {

                            include ('db.php');

                            $sql = "INSERT INTO cart (id, product_name, product_price) Values (1,'brownies',45000)";
                            $pdo->exec($sql);

                        
                        $textMessageBuilder1 = new TextMessageBuilder("added to cart");
                        $textMessageBuilder2 = new TextMessageBuilder('ketik keyword "cart" untuk melihat pemesanan anda');

                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($textMessageBuilder1);
                        $multiMessageBuilder->add($textMessageBuilder2);

                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    } else {
             
                    $result = $bot->replyText($event['replyToken'], "keyword yang anda masukan tidak sesuai, berikut adalah daftar keyword (menu, cart, cancel)");
                
                    }

                    $response->getBody()->write($result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                    
                }else if($event['message']['type'] == 'image' or $event['message']['type'] == 'video' or $event['message']['type'] == 'audio' or $event['message']['type'] == 'file'){

                    $textMessageBuilder1 = new TextMessageBuilder('maaf kami tidak menerima format data selain text');
                    $textMessageBuilder2 = new TextMessageBuilder('berikut adalah daftar keyword (menu, cart, cancel)');

                    $multiMessageBuilder = new MultiMessageBuilder();
                    $multiMessageBuilder->add($textMessageBuilder1);
                    $multiMessageBuilder->add($textMessageBuilder2);

                    $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    $response->getBody()->write((string) $result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());

                }
            }
        }
    }
 
});
$app->run();
