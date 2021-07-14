<?php

    require_once('config/TwitterAPIExchange.php');

    class ApiTwitter
    {
        /**
        * Array con datos de conexion a Twitter
        * @access private
        * @var array
        */
        private static $arr_configuration = array();

        /**
         * URL para publicar twitts
         * @access private
         * @var string
         */
        private static $url_publish = '';

        /**
         * URL para publicar imagenes
         * @access private
         * @var string
         */
        private static $url_publish_media = '';

        /**
         * URL para acceder al timeline del usuario
         * @access private
         * @var string
         */
        private static $url_user_timeline = '';

        /**
         * URL para buscar twitts por usuario y / o hastag
         * @access private
         * @var string
         */
        private static $url_search = '';

        /**
         * Limite caracteres Twitter
         * @access private
         * @var int
         */
        private static $tweet_char_limit = 0;


        /**
         * El constructor nunca será llamado
         * Esto impide la instanciación de la clase.
         * Ya que es una clase con métodos estáticos.
         */
        private function __construct()
        {
        }

        private static function initialize()
        {
            self:: $arr_configuration = parse_ini_file('config/configuration.ini');
            self:: $url_publish = 'https://api.twitter.com/1.1/statuses/update.json';
            self:: $url_publish_media = 'https://upload.twitter.com/1.1/media/upload.json';
            ;
            self:: $url_user_timeline = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
            self:: $url_search = 'https://api.twitter.com/1.1/search/tweets.json';
            self:: $tweet_char_limit = 280;
        }

        /**
         * Publica twiter solo texto o imagen o vídeo
         *
         * Este método se usa para publicar tweets con o sin imñagenes
         *
         * @access public
         * @param string $mensaje Texto del tweet obligatorio
         * @ruta_imagen string $ruta_imagen Ruta de la imagen para publicar en el tweet, opcional
         * @return publicado
         */

        public static function publishTwitter($mensaje, $ruta_imagen = null)
        {
            self::initialize();
            try {
                if (empty($mensaje) or is_null($mensaje)) {
                    throw new InvalidArgumentException('El texto del Tweet está vacío');
                } elseif (strlen($mensaje) > self::$tweet_char_limit) {
                    throw new InvalidArgumentException('EL texto del twitter no debe superar los ' . self::$tweet_char_limit. ' caracteres.');
                }
                $twitter = new TwitterAPIExchange(self::$arr_configuration);

                //Twitear imagen
                if (!is_null($ruta_imagen) && file_exists($ruta_imagen)) {
                    $postfields = array('media_data' => base64_encode(file_get_contents($ruta_imagen)));
                    $response = $twitter->buildOauth(self::$url_publish_media, 'POST')
                            ->setPostfields($postfields)
                            ->performRequest();
                    $postfields = array('status' => $mensaje,'media_ids' => json_decode($response)->media_id_string);
                    unset($response);
                } else {
                    $postfields = array('status' => $mensaje);
                }
                $result_twitter = $twitter->buildOauth(self::$url_publish, 'POST')
                            ->setPostfields($postfields)->performRequest();

                $result = json_decode($result_twitter);
                unset($result_twitter,$postfields);

                $publicado = true;

                if (isset($result->errors)) {
                    throw new Exception($result->errors[0]->message);
                }
            } catch (Exception $e) {
                echo 'Ha habido un error al publicar en twitter: ',  $e->getMessage(), "\n";
                $publicado = false;
            }
            return $publicado;
        }

        /**
         * Obtiene las menciones de un tweet
         *
         * @access private
         * @param json $mentions Menciones que tiene el tweet

         * @return arr_menciones
         */
        private function getTweetMentions($mentions)
        {
            $arr_menciones = array();
            foreach ($mentions as $mention) {
                $arr_menciones[] =  $mention->name;
            }
            return $arr_menciones;
        }

        /**
         * Obtiene las imágenes de un tweet
         *
         * @access private
         * @param json $media Imágenes que tiene el tweet

         * @return arr_media
         */
        private function getTweetMedia($media)
        {
            $arr_media = array();
            foreach ($media as $value) {
                $arr_media[] =  $value->media_url_https;
            }
            return $arr_media;
        }

        /**
         * Obtiene los hastags de un tweet
         *
         * @access private
         * @param json $hastags Hastags que tiene el tweet

         * @return arr_hastags
         */
        private function getTweetHastags($hastags)
        {
            $arr_hastags = array();

            foreach ($hastags as $hastag) {
                $arr_hastags[] = $hastag->text;
            }
            return $arr_hastags;
        }

        public static function searchByUser($user)
        {
            self::initialize();
            $twitter = new TwitterAPIExchange(self:: $arr_configuration);
            $response =  $twitter->setGetfield('?q=from:' . $user)
                                  ->buildOauth(self:: $url_search, 'GET')->performRequest();
            return self::getFormatedTweets(json_decode($response)->statuses);
        }

        public static function searchByHastags($arr_hastags)
        {
            self::initialize();
            $twitter = new TwitterAPIExchange(self:: $arr_configuration);
            $response =  $twitter->setGetfield('?q=' . implode('+OR+', $arr_hastags))
                                  ->buildOauth(self:: $url_search, 'GET')->performRequest();
            return self::getFormatedTweets(json_decode($response)->statuses);
        }

        /**
         * Obtiene los tweets del usuario
         *
         * @access public
         * @param int $count Total tweets a recuperar
         * @return result_twitts
         */
        public static function getUserTuitts($count = 20)
        {
            self::initialize();
            $twitter = new TwitterAPIExchange(self::$arr_configuration);
            $arr_Tweets = json_decode($twitter->setGetfield('?count=' . $count)
                                            ->buildOauth(self:: $url_user_timeline, 'GET')
                                            ->performRequest());

            return self::getFormatedTweets($arr_Tweets);
        }

        private function getFormatedTweets($arr_Tweets)
        {
            $result_twitts = array();
            foreach ($arr_Tweets as $key => $tweet) {
                $result_twitts[$key]['date'] = date('d-m-Y', strtotime($tweet->created_at));
                $result_twitts[$key]['text'] = $tweet->text;
                $result_twitts[$key]['mentions'] = self::getTweetMentions($tweet->entities->user_mentions);
                $result_twitts[$key]['hastags'] = self::getTweetHastags($tweet->entities->hashtags);
                $result_twitts[$key]['favs'] = $tweet->favorite_count;
                $result_twitts[$key]['retweets'] = $tweet->retweet_count;
                $result_twitts[$key]['favorited'] = $tweet->favorited;
                $result_twitts[$key]['retweeted'] = $tweet->retweeted;
                $result_twitts[$key]['user-name'] = $tweet->user->name;
                $result_twitts[$key]['screen-name'] = $tweet->user->screen_name;
                $result_twitts[$key]['user-location'] = $tweet->user->location;
                $result_twitts[$key]['user-description'] = $tweet->user->description;
                $result_twitts[$key]['followers'] = $tweet->user->followers_count;
                $result_twitts[$key]['firends'] = $tweet->user->friends_count;
                $result_twitts[$key]['media'] = '';

                if (isset($tweet->entities->media)) {
                    $result_twitts[$key]['media'] = self::getTweetMedia($tweet->entities->media);
                }
            }
            return $result_twitts;
        }
    }
