<?php

namespace App\Services;

use AllowDynamicProperties;
use App\TgEntities\InputMedia\InputMediaInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class Request
 *
 * @method void sendMessage(array $data)                     Use this method to send text messages. On success, the sent Message is returned.
 * @method void getUpdates(array $data)                      Use this method to receive incoming updates using long polling (wiki). An Array of Update objects is returned.
 * @method void setWebhook(array $data)                      Use this method to specify a url and receive incoming updates via an outgoing webhook. Whenever there is an update for the bot, we will send an HTTPS POST request to the specified url, containing a JSON-serialized Update. In case of an unsuccessful request, we will give up after a reasonable amount of attempts. Returns true.
 * @method void deleteWebhook(array $data)                   Use this method to remove webhook integration if you decide to switch back to getUpdates. Returns True on success.
 * @method void forwardMessage(array $data)                  Use this method to forward messages of any kind. On success, the sent Message is returned.
 * @method void forwardMessages(array $data)                 Use this method to forward multiple messages of any kind. If some of the specified messages can't be found or forwarded, they are skipped. Service messages and messages with protected content can't be forwarded. Album grouping is kept for forwarded messages. On success, an array of MessageId of the sent messages is returned.
 * @method void copyMessage(array $data)                     Use this method to copy messages of any kind. The method is analogous to the method forwardMessages, but the copied message doesn't have a link to the original message. Returns the MessageId of the sent message on success.
 * @method void copyMessages(array $data)                    Use this method to copy messages of any kind. If some of the specified messages can't be found or copied, they are skipped. Service messages, giveaway messages, giveaway winners messages, and invoice messages can't be copied. A quiz poll can be copied only if the value of the field correct_option_id is known to the bot. The method is analogous to the method forwardMessages, but the copied messages don't have a link to the original message. Album grouping is kept for copied messages. On success, an array of MessageId of the sent messages is returned.
 * @method void sendPhoto(array $data)                       Use this method to send photos. On success, the sent Message is returned.
 * @method void sendAudio(array $data)                       Use this method to send audio files, if you want TgEntities clients to display them in the music player. Your audio must be in the .mp3 format. On success, the sent Message is returned. Bots can currently send audio files of up to 50 MB in size, this limit may be changed in the future.
 * @method void sendDocument(array $data)                    Use this method to send general files. On success, the sent Message is returned. Bots can currently send files of any type of up to 50 MB in size, this limit may be changed in the future.
 * @method void sendVideo(array $data)                       Use this method to send video files, TgEntities clients support mp4 videos (other formats may be sent as Document). On success, the sent Message is returned. Bots can currently send video files of up to 50 MB in size, this limit may be changed in the future.
 * @method void sendAnimation(array $data)                   Use this method to send animation files (GIF or H.264/MPEG-4 AVC video without sound). On success, the sent Message is returned. Bots can currently send animation files of up to 50 MB in size, this limit may be changed in the future.
 * @method void sendVoice(array $data)                       Use this method to send audio files, if you want TgEntities clients to display the file as a playable voice message. For this to work, your audio must be in an .ogg file encoded with OPUS (other formats may be sent as Audio or Document). On success, the sent Message is returned. Bots can currently send voice messages of up to 50 MB in size, this limit may be changed in the future.
 * @method void sendVideoNote(array $data)                   Use this method to send video messages. On success, the sent Message is returned.
 * @method void sendMediaGroup(array $data)                  Use this method to send a group of photos or videos as an album. On success, an array of the sent Messages is returned.
 * @method void sendVenue(array $data)                       Use this method to send information about a venue. On success, the sent Message is returned.
 * @method void sendContact(array $data)                     Use this method to send phone contacts. On success, the sent Message is returned.
 * @method void sendChatAction(array $data)                  Use this method when you need to tell the user that something is happening on the bot's side. The status is set for 5 seconds or less (when a message arrives from your bot, TgEntities clients clear its typing status). Returns True on success.
 * @method void setMessageReaction(array $data)              Use this method to change the chosen reactions on a message. Service messages can't be reacted to. Automatically forwarded messages from a channel to its discussion group have the same available reactions as messages in the channel. Returns True on success.
 * @method void getUserProfilePhotos(array $data)            Use this method to get a list of profile pictures for a user. Returns a UserProfilePhotos object.
 * @method void getFile(array $data)                         Use this method to get basic info about a file and prepare it for downloading. For the moment, bots can download files of up to 20MB in size. On success, a File object is returned. The file can then be downloaded via the link https://api.telegram.org/file/bot<token>/<file_path>, where <file_path> is taken from the response. It is guaranteed that the link will be valid for at least 1 hour. When the link expires, a new one can be requested by calling getFile again.
 * @method void banChatMember(array $data)                   Use this method to kick a user from a group, a supergroup or a channel. In the case of supergroups and channels, the user will not be able to return to the group on their own using invite links, etc., unless unbanned first. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns True on success.
 * @method void unbanChatMember(array $data)                 Use this method to unban a previously kicked user in a supergroup or channel. The user will not return to the group or channel automatically, but will be able to join via link, etc. The bot must be an administrator for this to work. Returns True on success.
 * @method void restrictChatMember(array $data)              Use this method to restrict a user in a supergroup. The bot must be an administrator in the supergroup for this to work and must have the appropriate admin rights. Pass True for all permissions to lift restrictions from a user. Returns True on success.
 * @method void promoteChatMember(array $data)               Use this method to promote or demote a user in a supergroup or a channel. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Pass False for all boolean parameters to demote a user. Returns True on success.
 * @method void setChatAdministratorCustomTitle(array $data) Use this method to set a custom title for an administrator in a supergroup promoted by the bot. Returns True on success.
 * @method void banChatSenderChat(array $data)               Use this method to ban a channel chat in a supergroup or a channel. Until the chat is unbanned, the owner of the banned chat won't be able to send messages on behalf of any of their channels. The bot must be an administrator in the supergroup or channel for this to work and must have the appropriate administrator rights. Returns True on success.
 * @method void unbanChatSenderChat(array $data)             Use this method to unban a previously banned channel chat in a supergroup or channel. The bot must be an administrator for this to work and must have the appropriate administrator rights. Returns True on success.
 * @method void setChatPermissions(array $data)              Use this method to set default chat permissions for all members. The bot must be an administrator in the group or a supergroup for this to work and must have the can_restrict_members admin rights. Returns True on success.
 * @method void exportChatInviteLink(array $data)            Use this method to generate a new invite link for a chat. Any previously generated link is revoked. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns the new invite link as String on success.
 * @method void createChatInviteLink(array $data)            Use this method to create an additional invite link for a chat. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. The link can be revoked using the method revokeChatInviteLink. Returns the new invite link as ChatInviteLink object.
 * @method void editChatInviteLink(array $data)              Use this method to edit a non-primary invite link created by the bot. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns the edited invite link as a ChatInviteLink object.
 * @method void revokeChatInviteLink(array $data)            Use this method to revoke an invite link created by the bot. If the primary link is revoked, a new link is automatically generated. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns the revoked invite link as ChatInviteLink object.
 * @method void approveChatJoinRequest(array $data)          Use this method to approve a chat join request. The bot must be an administrator in the chat for this to work and must have the can_invite_users administrator right. Returns True on success.
 * @method void declineChatJoinRequest(array $data)          Use this method to decline a chat join request. The bot must be an administrator in the chat for this to work and must have the can_invite_users administrator right. Returns True on success.
 * @method void setChatPhoto(array $data)                    Use this method to set a new profile photo for the chat. Photos can't be changed for private chats. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns True on success.
 * @method void deleteChatPhoto(array $data)                 Use this method to delete a chat photo. Photos can't be changed for private chats. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns True on success.
 * @method void setChatTitle(array $data)                    Use this method to change the title of a chat. Titles can't be changed for private chats. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns True on success.
 * @method void setChatDescription(array $data)              Use this method to change the description of a group, a supergroup or a channel. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Returns True on success.
 * @method void pinChatMessage(array $data)                  Use this method to pin a message in a supergroup or a channel. The bot must be an administrator in the chat for this to work and must have the ‘can_pin_messages’ admin right in the supergroup or ‘can_edit_messages’ admin right in the channel. Returns True on success.
 * @method void unpinChatMessage(array $data)                Use this method to unpin a message in a supergroup or a channel. The bot must be an administrator in the chat for this to work and must have the ‘can_pin_messages’ admin right in the supergroup or ‘can_edit_messages’ admin right in the channel. Returns True on success.
 * @method void unpinAllChatMessages(array $data)            Use this method to clear the list of pinned messages in a chat. If the chat is not a private chat, the bot must be an administrator in the chat for this to work and must have the 'can_pin_messages' admin right in a supergroup or 'can_edit_messages' admin right in a channel. Returns True on success.
 * @method void leaveChat(array $data)                       Use this method for your bot to leave a group, supergroup or channel. Returns True on success.
 * @method void getChat(array $data)                         Use this method to get up to date information about the chat (current name of the user for one-on-one conversations, current username of a user, group or channel, etc.). Returns a Chat object on success.
 * @method void getChatAdministrators(array $data)           Use this method to get a list of administrators in a chat. On success, returns an Array of ChatMember objects that contains information about all chat administrators except other bots. If the chat is a group or a supergroup and no administrators were appointed, only the creator will be returned.
 * @method void getChatMemberCount(array $data)              Use this method to get the number of members in a chat. Returns Int on success.
 * @method void getChatMember(array $data)                   Use this method to get information about a member of a chat. Returns a ChatMember object on success.
 * @method void setChatStickerSet(array $data)               Use this method to set a new group sticker set for a supergroup. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Use the field can_set_sticker_set optionally returned in getChat requests to check if the bot can use this method. Returns True on success.
 * @method void deleteChatStickerSet(array $data)            Use this method to delete a group sticker set from a supergroup. The bot must be an administrator in the chat for this to work and must have the appropriate admin rights. Use the field can_set_sticker_set optionally returned in getChat requests to check if the bot can use this method. Returns True on success.
 * @method void getForumTopicIconStickers(array $data)       Use this method to get custom emoji stickers, which can be used as a forum topic icon by any user. Requires no parameters. Returns an Array of Sticker objects
 * @method void createForumTopic(array $data)                Use this method to create a topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights. Returns information about the created topic as a ForumTopic object.
 * @method void editForumTopic(array $data)                  Use this method to edit name and icon of a topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have can_manage_topics administrator rights, unless it is the creator of the topic. Returns True on success.
 * @method void closeForumTopic(array $data)                 Use this method to close an open topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights, unless it is the creator of the topic. Returns True on success.
 * @method void reopenForumTopic(array $data)                Use this method to reopen a closed topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights, unless it is the creator of the topic. Returns True on success.
 * @method void deleteForumTopic(array $data)                Use this method to delete a forum topic along with all its messages in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_delete_messages administrator rights. Returns True on success.
 * @method void unpinAllForumTopicMessages(array $data)      Use this method to clear the list of pinned messages in a forum topic. The bot must be an administrator in the chat for this to work and must have the can_pin_messages administrator right in the supergroup. Returns True on success.
 * @method void editGeneralForumTopic(array $data)           Use this method to edit the name of the 'General' topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have can_manage_topics administrator rights. Returns True on success.
 * @method void closeGeneralForumTopic(array $data)          Use this method to close an open 'General' topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights. Returns True on success.
 * @method void reopenGeneralForumTopic(array $data)         Use this method to reopen a closed 'General' topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights. The topic will be automatically unhidden if it was hidden. Returns True on success.
 * @method void hideGeneralForumTopic(array $data)           Use this method to hide the 'General' topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights. The topic will be automatically closed if it was open. Returns True on success.
 * @method void unhideGeneralForumTopic(array $data)         Use this method to unhide the 'General' topic in a forum supergroup chat. The bot must be an administrator in the chat for this to work and must have the can_manage_topics administrator rights. Returns True on success.
 * @method void unpinAllGeneralForumTopicMessages(array $data) Use this method to clear the list of pinned messages in a General forum topic. The bot must be an administrator in the chat for this to work and must have the can_pin_messages administrator right in the supergroup. Returns True on success.
 * @method void answerCallbackQuery(array $data)             Use this method to send answers to callback queries sent from inline keyboards. The answer will be displayed to the user as a notification at the top of the chat screen or as an alert. On success, True is returned.
 * @method void answerInlineQuery(array $data)               Use this method to send answers to an inline query. On success, True is returned.
 * @method void getUserChatBoosts(array $data)               Use this method to get the list of boosts added to a chat by a user. Requires administrator rights in the chat. Returns a UserChatBoosts object.
 * @method void setMyCommands(array $data)                   Use this method to change the list of the bot's commands. Returns True on success.
 * @method void deleteMyCommands(array $data)                Use this method to delete the list of the bot's commands for the given scope and user language. After deletion, higher level commands will be shown to affected users. Returns True on success.
 * @method void getMyCommands(array $data)                   Use this method to get the current list of the bot's commands. Requires no parameters. Returns Array of BotCommand on success.
 * @method void setMyName(array $data)                       Use this method to change the bot's name. Returns True on success.
 * @method void getMyName(array $data)                       Use this method to get the current bot name for the given user language. Returns BotName on success.
 * @method void setMyDescription(array $data)                Use this method to change the bot's description, which is shown in the chat with the bot if the chat is empty. Returns True on success.
 * @method void getMyDescription(array $data)                Use this method to get the current bot description for the given user language. Returns BotDescription on success.
 * @method void setMyShortDescription(array $data)           Use this method to change the bot's short description, which is shown on the bot's profile page and is sent together with the link when users share the bot. Returns True on success.
 * @method void getMyShortDescription(array $data)           Use this method to get the current bot short description for the given user language. Returns BotShortDescription on success.
 * @method void setChatMenuButton(array $data)               Use this method to change the bot's menu button in a private chat, or the default menu button. Returns True on success.
 * @method void getChatMenuButton(array $data)               Use this method to get the current value of the bot's menu button in a private chat, or the default menu button. Returns MenuButton on success.
 * @method void setMyDefaultAdministratorRights(array $data) Use this method to change the default administrator rights requested by the bot when it's added as an administrator to groups or channels. These rights will be suggested to users, but they are are free to modify the list before adding the bot. Returns True on success.
 * @method void getMyDefaultAdministratorRights(array $data) Use this method to get the current default administrator rights of the bot. Returns ChatAdministratorRights on success.
 * @method void editMessageText(array $data)                 Use this method to edit text and game messages sent by the bot or via the bot (for inline bots). On success, if edited message is sent by the bot, the edited Message is returned, otherwise True is returned.
 * @method void editMessageCaption(array $data)              Use this method to edit captions of messages sent by the bot or via the bot (for inline bots). On success, if edited message is sent by the bot, the edited Message is returned, otherwise True is returned.
 * @method void editMessageMedia(array $data)                Use this method to edit audio, document, photo, or video messages. On success, if the edited message was sent by the bot, the edited Message is returned, otherwise True is returned.
 * @method void editMessageReplyMarkup(array $data)          Use this method to edit only the reply markup of messages sent by the bot or via the bot (for inline bots). On success, if edited message is sent by the bot, the edited Message is returned, otherwise True is returned.
 * @method void stopPoll(array $data)                        Use this method to stop a poll which was sent by the bot. On success, the stopped Poll with the final results is returned.
 * @method void deleteMessage(array $data)                   Use this method to delete a message, including service messages, with certain limitations. Returns True on success.
 * @method void deleteMessages(array $data)                  Use this method to delete multiple messages simultaneously. If some of the specified messages can't be found, they are skipped. Returns True on success.
 * @method void getStickerSet(array $data)                   Use this method to get a sticker set. On success, a StickerSet object is returned.
 * @method void getCustomEmojiStickers(array $data)          Use this method to get information about custom emoji stickers by their identifiers. Returns an Array of Sticker objects.
 * @method void uploadStickerFile(array $data)               Use this method to upload a .png file with a sticker for later use in createNewStickerSet and addStickerToSet methods (can be used multiple times). Returns the uploaded File on success.
 * @method void createNewStickerSet(array $data)             Use this method to create new sticker set owned by a user. The bot will be able to edit the created sticker set. Returns True on success.
 * @method void addStickerToSet(array $data)                 Use this method to add a new sticker to a set created by the bot. Returns True on success.
 * @method void setStickerPositionInSet(array $data)         Use this method to move a sticker in a set created by the bot to a specific position. Returns True on success.
 * @method void deleteStickerFromSet(array $data)            Use this method to delete a sticker from a set created by the bot. Returns True on success.
 * @method void setStickerEmojiList(array $data)             Use this method to change the list of emoji assigned to a regular or custom emoji sticker. The sticker must belong to a sticker set created by the bot. Returns True on success.
 * @method void setStickerKeywords(array $data)              Use this method to change search keywords assigned to a regular or custom emoji sticker. The sticker must belong to a sticker set created by the bot. Returns True on success.
 * @method void setStickerMaskPosition(array $data)          Use this method to change the mask position of a mask sticker. The sticker must belong to a sticker set that was created by the bot. Returns True on success.
 * @method void setStickerSetTitle(array $data)              Use this method to set the title of a created sticker set. Returns True on success.
 * @method void setStickerSetThumbnail(array $data)          Use this method to set the thumbnail of a sticker set. Animated thumbnails can be set for animated sticker sets only. Returns True on success.
 * @method void setCustomEmojiStickerSetThumbnail(array $data) Use this method to set the thumbnail of a custom emoji sticker set. Returns True on success.
 * @method void deleteStickerSet(array $data)                Use this method to delete a sticker set that was created by the bot. Returns True on success.
 * @method void answerWebAppQuery(array $data)               Use this method to set the result of an interaction with a Web App and send a corresponding message on behalf of the user to the chat from which the query originated. On success, a SentWebAppMessage object is returned.
 * @method void sendInvoice(array $data)                     Use this method to send invoices. On success, the sent Message is returned.
 * @method void createInvoiceLink(array $data)               Use this method to create a link for an invoice. Returns the created invoice link as String on success.
 * @method void answerShippingQuery(array $data)             If you sent an invoice requesting a shipping address and the parameter is_flexible was specified, the Bot API will send an Update with a shipping_query field to the bot. Use this method to reply to shipping queries. On success, True is returned.
 * @method void answerPreCheckoutQuery(array $data)          Once the user has confirmed their payment and shipping details, the Bot API sends the final confirmation in the form of an Update with the field pre_checkout_query. Use this method to respond to such pre-checkout queries. On success, True is returned.
 * @method void setPassportDataErrors(array $data)           Informs a user that some of the TgEntities Passport elements they provided contains errors. The user will not be able to re-submit their Passport to you until the errors are fixed (the contents of the field for which you returned the error must change). Returns True on success. Use this if the data submitted by the user doesn't satisfy the standards your service requires for any reason. For example, if a birthday date seems invalid, a submitted document is blurry, a scan shows evidence of tampering, etc. Supply some details in the error message to make sure the user knows how to correct the issues.
 * @method void sendGame(array $data)                        Use this method to send a game. On success, the sent Message is returned.
 * @method void setGameScore(array $data)                    Use this method to set the score of the specified user in a game. On success, if the message was sent by the bot, returns the edited Message, otherwise returns True. Returns an error, if the new score is not greater than the user's current score in the chat and force is False.
 * @method void getGameHighScores(array $data)               Use this method to get data for high score tables. Will return the score of the specified user and several of his neighbors in a game. On success, returns an Array of GameHighScore objects.
 */
#[AllowDynamicProperties] class TgBotService
{
    /**
     * Available fields for InputFile helper
     *
     * This is basically the list of all fields that allow InputFile objects
     * for which input can be simplified by providing local path directly as string.
     *
     * @var array
     */
    private static $input_file_fields = [
        'setWebhook' => ['certificate'],
        'sendPhoto' => ['photo'],
        'sendAudio' => ['audio', 'thumbnail'],
        'sendDocument' => ['document', 'thumbnail'],
        'sendVideo' => ['video', 'thumbnail'],
        'sendAnimation' => ['animation', 'thumbnail'],
        'sendVoice' => ['voice'],
        'sendVideoNote' => ['video_note', 'thumbnail'],
        'setChatPhoto' => ['photo'],
        'sendSticker' => ['sticker'],
        'uploadStickerFile' => ['sticker'],
        'setStickerSetThumbnail' => ['thumbnail'],
    ];

    private static string $current_action = '';
    private HttpClientInterface $httpClient;

    public function __construct(
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        string $api_key,
        string $api_url
    ) {
        if (empty($api_key)) {
            throw new \Exception('API KEY not defined!');
        }
        preg_match('/(\d+):[\w\-]+/', $api_key, $matches);
        if (!isset($matches[1])) {
            throw new \Exception('Invalid API KEY defined!');
        }
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->bot_id = (int)$matches[1];
        $this->api_key = $api_key;
        $this->api_url = $api_url;
    }

    public function __call($name, $arguments)
    {
        $this->send($name, $arguments[0]);
    }

    public function send(string $action, array $data = [])
    {
        // ensure no empty data
        if (count($data) === 0) {
            throw new \Exception('Data is empty!');
        }
        // Remember which action is currently being executed.
        self::$current_action = $action;

        $raw_response = $this->execute($action, $data);
        $response = json_decode($raw_response, true);
        $this->logger->debug($raw_response, ['tgbot_send']);
        if (null === $response) {
            $this->logger->debug($raw_response);
            throw new \Exception('Telegram returned an invalid response!');
        }
        //TODO: verificar esto luego....
//        $response = new ServerResponse($response);
//        if (!$response->isOk() && $response->getErrorCode() === 401 && $response->getDescription() === 'Unauthorized') {
//            throw new \Exception('Invalid bot token!');
//        }
    }

    /**
     * Execute HTTP Request
     *
     * @param string $action Action to execute
     * @param array $data Data to attach to the execution
     *
     * @return string Result of the HTTP Request
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function execute(string $action, array $data = []): string
    {
        $request_params = $this->setUpRequestParams($data);
        $response = $this->httpClient->request(
            'POST',
            $this->api_url.'/bot'.$this->api_key.'/'.$action,
            $request_params
        );
        $result = $response->getContent();
        // check for errors
        $level = (int)floor($response->getStatusCode() / 100);
        if ($level != 2) {
            if ($level === 4) {
                $label = 'Client error';
                $className = ClientException::class;
            } elseif ($level === 5) {
                $label = 'Server error';
                $className = ServerException::class;
            } else {
                $label = 'Unsuccessful request';
                $className = \Exception::class;
            }
            throw new $className($label, $response->getStatusCode());
        }
//        $this->logger->debug('Request data:'.PHP_EOL.print_r($data, true), ['tgbot_exec']);
//        $this->logger->debug('Response data:'.PHP_EOL.$result, ['tgbot_exec']);

        return $result;
    }

    public function setUpRequestParams(array $data): array
    {
        $has_resource = false;
        $multipart = [];

        foreach ($data as $key => &$item) {
            if ($key === 'media') {
                // Magical media input helper.
                $item = $this->mediaInputHelper($item, $has_resource, $multipart);
            } elseif (array_key_exists(self::$current_action, self::$input_file_fields) && in_array(
                    $key,
                    self::$input_file_fields[self::$current_action],
                    true
                )) {
                // Allow absolute paths to local files.
                if (is_string($item) && file_exists($item)) {
//                    $item = new Stream(self::encodeFile($item));
                }
            }
            // Reformat data array in multipart way if it contains a resource
            $has_resource = $has_resource || is_resource($item);
            //$has_resource = $has_resource || is_resource($item) || $item instanceof Stream;
            $multipart[] = ['name' => $key, 'contents' => $item];
        }
        unset($item);

        if ($has_resource) {
            return ['multipart' => $multipart];
        }

        return ['json' => $data];
    }

    public function mediaInputHelper(mixed $item, bool $has_resource, array $multipart)
    {
        $was_array = is_array($item);
        $was_array || $item = [$item];
        foreach ($item as $media_item) {
            if (!($media_item instanceof InputMediaInterface)) {
                continue;
            }

            // Make a list of all possible media that can be handled by the helper.
            $possible_medias = array_filter([
                'media' => $media_item->getMedia(),
                'thumbnail' => $media_item->getThumbnail(),
            ]);

            foreach ($possible_medias as $type => $media) {
                // Allow absolute paths to local files.
                /*if (is_string($media) && !str_starts_with($media, 'attach://') && file_exists($media)) {
                    $media = new Stream(self::encodeFile($media));
                }*/

                //if (is_resource($media) || $media instanceof Stream) {
                if (is_resource($media)) {
                    $has_resource = true;
                    $unique_key = uniqid($type.'_', false);
                    $multipart[] = ['name' => $unique_key, 'contents' => $media];

                    // We're literally overwriting the passed media type data!
                    $media_item->$type = 'attach://'.$unique_key;
                    $media_item->raw_data[$type] = 'attach://'.$unique_key;
                }
            }
        }

        $was_array || $item = reset($item);

        return json_encode($item);
    }

}