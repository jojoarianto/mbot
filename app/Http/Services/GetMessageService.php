<?php

namespace App\Http\Services;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use App\Mahasiswa;

class GetMessageService
{
    /**
     * @var LINEBot
     */
    private $bot;
    /**
     * @var HTTPClient
     */
    private $client;
    
    
    public function replySend($formData)
    {
        $replyToken = $formData['events']['0']['replyToken'];
        
        $this->client = new CurlHTTPClient("W8tQwPykagJeHU19x2GoqkiCj9l9P6ofSdfA2TWGvfwucMcnVXecX59aBs7lqfU45OjRtpkABbqZNgqDQk8KTTZEaNHJf/MWKENVWaFkj3q2sO/2eiOu7xa739J4LGsHmSZP8k8UnAyEOTExEdisvgdB04t89/1O/w1cDnyilFU=");
        $this->bot = new LINEBot($this->client, ['channelSecret' => "7f103fc401ae13302013413730f6b606" ]);
        
        $msg = $formData['events']['0']['message'];
        logger( $msg['text'] );

        $msg['text'] = strtolower($msg['text']);
        $array = explode( " ", $msg['text'] );
        if(count($array) <= 1){
            if($array[0] == 'format'){
                $output = "Format chat: \n1. cari (spasi) [nama mahasiswa 2017]\n2. foto (spasi) [nim] \n\ncontoh: \n1. 'cari setyo novanto'\n2. 'foto 012345678923'";
                $response = $this->bot->replyText($replyToken, $output);
            }
        } else {
            if($array[0] == 'cari'){
                $t = substr($msg['text'], 5);
                $output = 'Kata kunci '.$t;
                $mhs = Mahasiswa::where('nama', 'like', '%' . $t . '%')->take(10)->get();
                if( $mhs->count() == 1 ) {
                    foreach ($mhs as $k => $mh) {
                        $output = "";
                        $output = $output . "NAMA : " . $mh->nama;
                        $output = $output . "\nNIM : " . $mh->nim;
                        $output = $output . "\nPRODI : " . $mh->prodi;

                        $url = "https://cybercampus.unair.ac.id/foto_mhs/".$mh->nim.".JPG";
                        $imageMessageBuilder = new LINEBot\MessageBuilder\ImageMessageBuilder($url, $url);
                        $textMessageBuilder1 = new LINEBot\MessageBuilder\TextMessageBuilder($output);
                        
                        $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();
                        $multiMessageBuilder->add($imageMessageBuilder);
                        $multiMessageBuilder->add($textMessageBuilder1);
                        
                        $this->bot->replyMessage($replyToken, $multiMessageBuilder);
                    }
                } else if($mhs->count() > 1 ) {
                    $n = Mahasiswa::where('nama', 'like', '%' . $t . '%')->get()->count();
                    $output = "Ditemukan " .$n. " data";
                    foreach ($mhs as $k => $mh) {
                        $output = $output . "\n(".($k+1).") " . $mh->nama;                    
                    }
                    $response = $this->bot->replyText($replyToken, $output);
                } else {
                    $output = $output . "\nTidak ditemukan";
                    $response = $this->bot->replyText($replyToken, $output);
                }
            } else if ($array[0] == 'foto') {
                // $output = "Fitur ini tidak dapat diakses untuk sementara";
                // $response = $this->bot->replyText($replyToken, $output);

                $t = substr($msg['text'], 5);

                $url = "https://cybercampus.unair.ac.id/foto_mhs/".$t.".JPG";
                $imageMessageBuilder = new LINEBot\MessageBuilder\ImageMessageBuilder($url, $url);
                // $textMessageBuilder1 = new LINEBot\MessageBuilder\TextMessageBuilder($output);
                
                $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();
                $multiMessageBuilder->add($imageMessageBuilder);
                // $multiMessageBuilder->add($textMessageBuilder1);

                $this->bot->replyMessage($replyToken, $multiMessageBuilder);                
            } else {
                $output = "Perintah yang anda masukkan salah";
                $response = $this->bot->replyText($replyToken, $output);   
            }
        }
        
        if ($response->isSucceeded()) {
            logger("reply success!!");
            return;
        }
    }
}