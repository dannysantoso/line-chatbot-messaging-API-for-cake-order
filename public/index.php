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
 
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "feKHIGbWLPTrDwVOs78WnYLd8++OvHvatC+67VAKZUExtDMBPArvxwO3c4LLHOwX4M7zd2twX0jS/lbGelJNSX6ZFbfsQsVS8FAlX5Q1T6uRfxnF5nJ1rbymm/pPLdbvw/Zm9Au+NjXQ2JbLHZ3bBQdB04t89/1O/w1cDnyilFU=";
$channel_secret = "7653ca3585b6f4cae2ec5315e0ceed1c";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});

//for push message
$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to user
    $userId = 'U72c3603ae0f3ab5ab6e814bdeb0dee67'; //ganti dengan user id
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);
 
    $response->getBody()->write((string) $result->getJSONDecodedBody());
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

// kalo mau pake sticker ngepush message
/*$userId = 'Isi dengan user ID Anda';
$stickerMessageBuilder = new StickerMessageBuilder(1, 106);
$bot->pushMessage($userId, $stickerMessageBuilder);*/
 
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
    
// kode aplikasi nanti disini

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

                        $result = $bot->replyText($event['replyToken'], data);
 
                    } else {
                    //balas pesan pakai sticker
                    /*packageId = 1;
                    $stickerId = 3;
                    $stickerMessageBuilder = new StickerMessageBuilder($packageId, $stickerId);
                    $bot->replyMessage($replyToken, $stickerMessageBuilder);*/
                    //replySticker(replyToken, 1, 1); cara cepetnya



                    // send same message as reply to user
                    $result = $bot->replyText($event['replyToken'], "keyword yang anda masukan tidak sesuai, berikut adalah daftar keyword (menu, cart, buy, cancel)");
                    

                    // sent different message as reply to user
                    /*$textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
                    $bot->replyMessage($replyToken, $textMessageBuilder);*/
                    //$bot->replyText($replyToken, 'ini pesan balasan'); cara cepetnya
     
     
                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);

                    //bales dengan multitext
                    /*$textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
                    $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
                    $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                     
                     
                    $multiMessageBuilder = new MultiMessageBuilder();
                    $multiMessageBuilder->add($textMessageBuilder1);
                    $multiMessageBuilder->add($textMessageBuilder2);
                    $multiMessageBuilder->add($stickerMessageBuilder);
                     
                     
                    $bot->replyMessage($replyToken, $multiMessageBuilder);*/


                    //kirim gambar
                    /*use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
 
                    $imageMessageBuilder = new ImageMessageBuilder('url gambar asli', 'url gambar preview');
                    $bot->replyMessage($replyToken, $imageMessageBuilder);*/

                    //kirim audio
                    /*use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
 
                    $audioMessageBuilder = new AudioMessageBuilder('url audio asli', 'durasi audio');
                    $bot->replyMessage($replyToken, $audioMessageBuilder);*/

                    //kirim video
                    /*use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
 
                    $videoMessageBuilder = new VideoMessageBuilder('url video asli', 'url gambar preview video');
                    $bot->replyMessage($replyToken, $videoMessageBuilder);*/
     
     
                    $response->getBody()->write($result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                    }
                }
            }
        }
    }
 
});
$app->run();