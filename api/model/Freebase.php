<?php

/**
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class Freebase
{
    /**
     * @var string
     */
    private $apiKey = "AIzaSyBkYwzDuYoap-Y4t6_cj3phG9RaVduJtM8";

    public function searchCompany($name)
    {
        $topic = $this->search($name);

        if ($topic == null) {
            return null;
        }

        $mid = $topic[0]['mid'];
        $result = $this->topic($mid);

        //Check if topic is a company
        $check = false;
        if (isset($result['property']['/common/topic/notable_types']['values'])) {
            $types = $result['property']['/common/topic/notable_types']['values'];

            foreach ($types as $type) {
                if ($type['id'] == '/business/business_operation') {
                    $check = true;
                }
            }
        }

        if ($check == false) {
            return null;
        }

        if (isset($result['property']['/common/topic/official_website']['values'][0]['text'])) {
            $website = $result['property']['/common/topic/official_website']['values'][0]['value'];
        } else {
            $website = null;
        }

        if (isset($result['property']['/common/topic/description']['values'][0]['text'])) {
            $description = $result['property']['/common/topic/description']['values'][0]['value'];
        } else {
            $description = null;
        }

        if (isset($result['property']['/organization/organization/leadership']['values'][0])) {
            $leadership = $result['property']['/organization/organization/leadership']['values'][0];

            if (isset($leadership['property']['/organization/leadership/person']['values'][0]['text'])) {
                $leadershipName = $leadership['property']['/organization/leadership/person']['values'][0]['text'];
                $leadershipId = $leadership['property']['/organization/leadership/person']['values'][0]['id'];
                $leadershipUrl = 'https://www.freebase.com' . $leadershipId;
            } else {
                $leadershipName = null;
            }

            if (isset($leadership['property']['/organization/leadership/role']['values'][0]['text'])) {
                $leadershipRole = $leadership['property']['/organization/leadership/role']['values'][0]['text'];
            } else {
                $leadershipRole = null;
            }

        } else {
            $leadershipId = null;
            $leadershipName = null;
            $leadershipRole = null;
        }

        $object = new stdClass();
        $object->url = 'https://www.freebase.com' . $result['id'];
        $object->website = $website;
        $object->description = $description;
        $object->image = $this->image($result['id']);

        $object->leadership = new stdClass();
        $object->leadership->url = $leadershipUrl;
        $object->leadership->name = $leadershipName;
        $object->leadership->role = $leadershipRole;
        $object->leadership->image = $this->image($leadershipId);

        return $object;
    }

    public function search($query = '', $start = 0, $limit = 1, $exact = 'false')
    {
        $query = urlencode($query);
        $url = 'https://www.googleapis.com/freebase/v1/search?query=' . $query;
        $url .= '&start=' . $start;
        $url .= '&limit=' . $limit;
        $url .= '&exact=' . $exact;
        $url .= '&key=' . $this->apiKey;

        $freebaseResults = @file_get_contents($url);

        if (!empty($freebaseResults)) {
            $decoded = json_decode($freebaseResults, true);

            return $decoded['result'];
        }
    }

    public function image($entityId, $maxWidth = 150, $maxHeight = 150)
    {
        $url = 'https://usercontent.googleapis.com/freebase/v1/image' . $entityId;
        $url .= '?maxwidth=' . $maxWidth;
        $url .= '&maxheight=' . $maxHeight;
        $url .= '&key=' . $this->apiKey;

        return $url;
    }

    public function text($entityId, $maxLength = '0')
    {
        $url = 'https://www.googleapis.com/freebase/v1/text/' . $entityId;
        $url .= '?maxlength=' . $maxLength;
        $url .= '&key=' . $this->apiKey;

        $freebase_results = @file_get_contents($url);

        if (!empty($freebase_results)) {
            $decoded = json_decode($freebase_results, true);

            return $decoded['result'];
        }
    }

    public function topic($entityId)
    {
        $url = 'https://www.googleapis.com/freebase/v1/topic' . $entityId;

        $freebase_results = @file_get_contents($url);

        if (!empty($freebase_results)) {
            return json_decode($freebase_results, true);
        }
    }
}
