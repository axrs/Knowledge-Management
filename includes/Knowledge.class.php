<?php

class KnowledgeManager
{
    /**
     * @var PDO Database
     */
    private static $_databaseHandle = null;
    protected $_knowledgeID = "";
    protected $_knowledgeData = array();
    private static $_table = "knowledge";
    private static $_idField = "knowledgeID";

    /**
     * Constructs a KnowledgeManager Class
     */
    function __construct($id = null)
    {
        if ($id != null && self::$_databaseHandle) {
            $this->load($id);
        }
    }

    /**
     * Sets the KnowledgeManager Class Database
     * @param PDO $database Sets the knowledge Manager database to a PDO database object.
     */
    public static function SetDatabase($database)
    {
        self::$_databaseHandle = $database;
    }

    /**
     * Gets a list of recently posted articles
     * @param int $limit number of articles to fetch
     * @return array KnowledgeManager article references
     */
    public static function GetRecentArticles($limit = 10)
    {
        $results = self::$_databaseHandle->prepare(
            sprintf("SELECT knowledgeID FROM '%s' LEFT JOIN '%s' ON '%s'='%s' ORDER BY '%s'.'%s' DESC LIMIT %s;",
                self::$_table, 'user', 'knowledgeAuthor', 'userID',self::$_table, 'knowledgeDate', $limit));
        $results->execute();
        $entries = array();
        foreach ($results as $result) {
            $knowledge = new KnowledgeManager($result[self::$_idField]);
            array_push($entries, $knowledge);
        }
        return $entries;
    }

    /**
     * Gets an array of tags for which an article ID has
     * @param int $articleID knowledge entry
     * @return array tags
     */
    public static function GetArticleTags($articleID)
    {
        $result = self::$_databaseHandle->prepare(
            sprintf("SELECT * FROM 'tags' LEFT JOIN 'tag' ON 'tags'.'tagID'='tag'.'tagID' WHERE 'tags'.'knowledgeID'='%s' ORDER BY 'tagName' DESC;",
                $articleID
            ));
        $result->execute();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTags()
    {
        return self::GetArticleTags($this->getProperty(self::$_idField));
    }

    public function getTagLinks()
    {
        $html = '';
        foreach ($this->getTags() as $tag) {
            $html .= sprintf('<a href="/search?tags=%s" title="%s">%s</a> ', $tag['tagName'], $tag['tagName'], $tag['tagName']);
        }
        return $html;
    }

    /**
     * Gets a CSV list of tags
     * @return string CSV list of article tags
     */
    public function getTagCSV()
    {
        $tags = array();
        foreach ($this->getTags() as $tag) {
            array_push($tags, $tag['tagName']);
        }
        return implode(', ', $tags);
    }

    /**
     * Gets the underlying KnowledgeManager table name
     * @return string
     */
    public static function GetTableName()
    {
        return self::$_table;
    }

    /**
     * Gets the current state of the KnowledgeManager
     * @return bool true if a knowledge article was loaded
     */
    public function isLoaded()
    {
        return !(empty($this->_knowledgeID));
    }

    /**
     * Loads a resource with the specified ID
     * @param int $id ID of the resource to load
     * @return bool True if load successful
     */
    function load($id)
    {
        $result = self::$_databaseHandle->prepare(
            sprintf("SELECT * FROM '%s' LEFT JOIN '%s' ON '%s'.'%s'='%s'.'%s' WHERE '%s'.'%s'='%s' LIMIT 1;",
                self::$_table,'user','knowledge','knowledgeAuthor','user','userID',self::$_table,self::$_idField,$id)
        );
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        //If there is no result, return false
        if (!$result) return false;
        //Otherwise load the content into the object
        $this->_knowledgeData = $result;
        $this->_knowledgeID = $id;
        return true;
    }

    /**Updates the current resource within the database
     * @param array $data Update data as an associative array
     * @return int|null Number of affected database rows
     */
    function update($data)
    {
        if (empty($this->_knowledgeID)) return null;
        if (!is_array($data)) return null;

        $insertKnowledge = self::$_databaseHandle->prepare(
            "UPDATE `knowledge` SET `knowledgeTitle` = ?, `knowledgeBody` = ? WHERE `knowledgeID` = ?;"
        );

        $result = $insertKnowledge->execute(
            array(
                strtoupper($data['knowledgeTitle']),
                Purification::Purify($data['knowledgeBody']),
                $this->getProperty('knowledgeID')
            )
        );
        if (!$result) return false;
        $this->setTags($data['tags']);
        return true;
    }

    /**Deletes the specified resource from the database.
     * @return int|null Number of affected database rows
     */
    function delete()
    {
        if (empty($this->_knowledgeID)) return null;
        $sql = "DELETE FROM content WHERE id = $this->_knowledgeID";
        return $this->_databaseHandle->exec($sql);
    }

    /**Creates a resource from the givendata
     * @param array $data Associative array indicating the resource
     * @return int|null Number of affected rows.
     */
    public static function Create($data)
    {
        if (!is_array($data)) return null;

        $insertKnowledge = self::$_databaseHandle->prepare(
            "INSERT INTO `knowledge` (`knowledgeTitle`,`knowledgeBody`,`knowledgeAuthor`,`knowledgeDate`) VALUES(?,?,?,?);"
        );

        $result = $insertKnowledge->execute(
            array(
                strtoupper($data['knowledgeTitle']),
                Purification::Purify($data['knowledgeBody']),
                $data['knowledgeAuthor'],
                date("Y-m-d H:i:s")
            )
        );
        if (!$result) return false;
        $article = new KnowledgeManager(self::$_databaseHandle->lastInsertId());
        $article->setTags($data['tags']);
        return $article;
    }

    /**
     * Creates/Sets the article tags
     * @param array $tags tags
     */
    private function setTags($tags)
    {
        $knowledgeID = $this->getProperty('knowledgeID');

        //Remove any existing Tags
        $sql = sprintf("DELETE FROM `tags` WHERE `knowledgeID` = %s;", $knowledgeID);
        self::$_databaseHandle->exec($sql);

        //Clean and trim tags
        $tags = explode(',', $tags);
        $tags = array_map('trim', $tags);
        $tags = array_map('strtoupper', $tags);
        $tagIDs = array();

        $sql = sprintf("SELECT * from `tag` WHERE `tagName` IN ('%s');", implode('\',\'', $tags));

        $results = self::$_databaseHandle->prepare($sql);
        $results->execute();

        //Fetch any existing TagIDs
        foreach ($results->fetchAll(PDO::FETCH_ASSOC) as $result) {
            if (($key = array_search($result['tagName'], $tags)) !== false) {
                unset($tags[$key]);
                array_push($tagIDs, $result['tagID']);
            }
        }
        //Clean the existing tags from the new
        $sql = sprintf("INSERT INTO `tag` (`tagName`) VALUES (?);");
        $query = self::$_databaseHandle->prepare($sql);
        foreach ($tags as $tag) {
            $query->execute(array($tag));
            array_push($tagIDs, self::$_databaseHandle->lastInsertId());
        }
        //Insert all the tag ID references
        $sql = sprintf("INSERT INTO `tags` (`tagID`,`knowledgeID`) VALUES (?,?);");
        $query = self::$_databaseHandle->prepare($sql);
        foreach ($tagIDs as $tag) {
            $query->execute(array($tag, $knowledgeID));
            $query->fetchAll();
        }
    }

    /**
     * Gets the current loaded resource as an array represenation
     * @return array Resource as an associative array
     */
    public function getArray()
    {
        return $this->_knowledgeData;
    }

    /**
     * Determines if the resource has a specified property
     * @param string $prop Resource property
     * @return bool True if the property exists
     */
    public function is($prop)
    {
        return $this->getProperty($prop) == 1 ? true : false;
    }

    /**
     * Gets a specified property
     * @param string $property Property key
     * @return string Property value
     */
    public function getProperty($property)
    {
        if (empty($this->_knowledgeData)) return "";
        if (!isset($this->_knowledgeData[$property])) return "";
        return $this->_knowledgeData[$property];
    }

    /**
     * Gets the article body as a summary
     * @param $maxWords Maximum word count
     * @param bool $stripTags remove html tags?
     * @return string article summary
     */
    public function getSummary($maxWords, $stripTags = true)
    {
        $summary = $this->getProperty('knowledgeBody');
        if ($stripTags) {
            $summary = strip_tags($summary);
        }
        return $this->_tokenTruncate($summary, $maxWords);
    }

    public function getURI()
    {
        return sprintf('/kb/%s', $this->getProperty(self::$_idField));
    }
    public function getLink()
    {
        return sprintf('<a href="%s" title="%s" rel="article">%s</a>',$this->getURI(),$this->getTitle(),$this->getTitle());
    }

    public function getTitle()
    {
        return $this->getProperty('knowledgeTitle');
    }

    public function getPublishedDate()
    {
        return $this->getProperty('knowledgeDate');
    }
    public function getBody()
    {
        return $this->getProperty('knowledgeBody');
    }

    public function getAuthor(){
        return sprintf('%s, %s',$this->getProperty('userSurname'),$this->getProperty('userFirstName'));
    }

    /**
     * Truncates a provided phrase or list of words
     * @param string $string paragraph to truncate
     * @param $wordLimit maximum word count
     * @return string truncated string
     */
    private static function _tokenTruncate($string, $wordLimit)
    {
        $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $wordLimit) {
                break;
            }
        }

        return implode(array_slice($parts, 0, $last_part));
    }

    /**
     * Queries the database for a list of tags
     * @return array list of tags with corresponding parent trunk, path and ID
     */
    private static function _buildTagTree()
    {

        $sql = '
SELECT knowledgeTags
FROM knowledge
LEFT JOIN (
	SELECT knowledgeID, GROUP_CONCAT(taggyName,",") as knowledgeTags
	FROM (
		SELECT *
		FROM tags
		LEFT JOIN (
			SELECT tag.tagID as taggyID, tag.tagName as taggyName, tagCount
			FROM tag
			LEFT JOIN (
				SELECT tagID, count(*) as tagCount
				FROM tags GROUP BY tagID
			) as c ON c.tagID = tag.tagID
		) ON taggyID = tags.tagID ORDER BY tagCount DESC, taggyName ASC
	) GROUP BY knowledgeID
) as t ON t.knowledgeID = knowledge.knowledgeID WHERE knowledgeTags NOT NULL ORDER BY knowledgeTags';

        $searchResults = self::$_databaseHandle->query($sql);
        $pageTags = $searchResults->fetchAll(PDO::FETCH_ASSOC);
        $tree = array();
        $tagPath = '';
        $previous = '';
        foreach ($pageTags as $tagSet) {
            $tagSet = explode(',', $tagSet['knowledgeTags']);
            foreach ($tagSet as $tag) {
                $tagPath = trim($tagPath . ',' . $tag);
                if (empty($previous)) {
                    $tree[$tagPath] = array(
                        'parent' => '',
                        'alias' => $tag,
                        'path' => $tagPath
                    );
                } else {
                    $tree[$tagPath] = array(
                        'parent' => $previous,
                        'alias' => $tag,
                        'path' => $tagPath
                    );
                }
                $previous = $tagPath;
            }
            $previous = '';
            $tagPath = '';
        }
        return $tree;
    }

    /**
     * Prints the tag tree to an unordered HTML list
     * @param string $trunk starting trunk to process
     */
    public static function PrintTagTreeHTML($trunk = '')
    {
        $tree = self::_buildTagTree();
        printf('<ul><li><a href="/">%s</a>', 'Knowledge');
        $html = self::_buildTagTreeBranch($tree, $trunk);
        print '<ul>' . $html . '</ul>';
        print "</li></ul>";
    }

    /**
     * Recursively builds branch of tags
     * @param array $tree tag tree to search
     * @param string $trunk tag trunk to match
     * @return string HTML formatted list
     */
    private static function _buildTagTreeBranch($tree, $trunk = '')
    {
        $html = '';

        if (count($tree) > 0) {
            foreach ($tree as $branch => $details) {
                if ($details['parent'] == $trunk) {
                    $result = self::_buildTagTreeBranch($tree, $branch);
                    $html .= sprintf('<li><a href="/search?tags=%s" title="Show Tag Results">%s</a>', $details['path'], $details['alias']);

                    if (!empty($result)) {
                        $html = $html . '<ul>' . $result . '</ul>';
                    }
                    $html .= '</li>';
                }
            }
        }
        return $html;
    }

    /**
     * Searches the knowledge database (tags, title and body) and returns a list of ranked results
     * @param string $search search term or phrase
     * @param int $offset article offset number (for paging)
     * @param int $limit article return limit
     * @return array KnowledgeManager matched articles
     */
    public static function SearchExtents($search, $offset = 0, $limit = 100)
    {
        $search = sqlite_escape_string($search);
        $searchTerms = array();
        if (strpos($search, ' ')) $searchTerms = explode(' ', $search);
        else $searchTerms[0] = $search;

        $sql = "SELECT *,";
        foreach ($searchTerms as $term) {
            $sql .= sprintf(" CASE WHEN knowledgeTitle LIKE '%s' THEN 3 ELSE 0 END +", '%' . $term . '%');
            $sql .= sprintf(" CASE WHEN knowledgeTags LIKE '%s' THEN 2 ELSE 0 END +", '%' . $term . '%');
            $sql .= sprintf(" CASE WHEN knowledgeBody LIKE '%s' THEN 1 ELSE 0 END +", '%' . $term . '%');
        }
        $sql = trim($sql, '+') . "AS ranking";
        $sql .= " FROM (
                        SELECT *
                        FROM knowledge LEFT JOIN (
                            SELECT knowledgeID, GROUP_CONCAT(tagName) as knowledgeTags
                            FROM (SELECT * FROM tags LEFT JOIN tag on tags.tagID = tag.tagID)
                            GROUP BY knowledgeID
                        )as t ON t.knowledgeID = knowledge.knowledgeID
                        LEFT JOIN user ON knowledgeAuthor = userID
                )";
        $sql .= sprintf(" WHERE ((knowledgeTitle LIKE '%s' OR knowledgeTags LIKE '%s' OR knowledgeBody LIKE '%s')", '%' . $searchTerms[0] . '%', '%' . $searchTerms[0] . '%', '%' . $searchTerms[0] . '%');

        for ($i = 1; $i < count($searchTerms); $i++) {
            $sql .= sprintf(" OR (knowledgeTitle LIKE '%s' OR knowledgeTags LIKE '%s' OR knowledgeBody LIKE '%s')", '%' . $searchTerms[$i] . '%', '%' . $searchTerms[$i] . '%', '%' . $searchTerms[$i] . '%');
        }
        $sql .= ") ORDER BY ranking DESC LIMIT $limit OFFSET $offset;";

        $articles = array();
        $searchResults = self::$_databaseHandle->query($sql);

        foreach ($searchResults->fetchAll(PDO::FETCH_ASSOC) as $result) {
            array_push($articles, new KnowledgeManager($result[self::$_idField]));
        }
        return $articles;
    }

    /**
     * Searches for any entries matching the provided tags
     * @param string $search CSV tag string
     * @param int $offset starting entry count to return
     * @param int $limit number of entries to return
     * @return array KnowledgeManager entries matching tags
     */
    public static function SearchTags($search, $offset = 0, $limit = 100)
    {
        $search = sqlite_escape_string($search);
        $search = explode(',', $search);

        $tagIDs = self::_tagNamesToTagIDs($search);

        if (count($tagIDs) == 0) {
            return array();
        }
        $case = '';
        foreach ($tagIDs as $term) {
            $case .= sprintf(" CASE WHEN tags.tagID = %s THEN 1 ELSE 0 END +", $term);
        }
        $case = trim($case, '+');
        $sql = sprintf('SELECT knowledge.knowledgeID FROM knowledge LEFT JOIN (SELECT tags.knowledgeID, tags.tagID, SUM(%s) AS ranking FROM tags GROUP BY tags.knowledgeID)as t on t.knowledgeID = knowledge.knowledgeID LEFT JOIN user ON user.userID = knowledge.knowledgeAuthor WHERE t.ranking = %s ORDER BY knowledge.knowledgeTitle;', $case, count($tagIDs));

        $articles = array();
        $searchResults = self::$_databaseHandle->query($sql);

        foreach ($searchResults->fetchAll(PDO::FETCH_ASSOC) as $result) {
            array_push($articles, new KnowledgeManager($result[self::$_idField]));
        }
        return $articles;
    }

    /**
     * Converts tag names to corresponding IDs
     * @param array $tags of tags to translate to IDs
     * @return array of tag IDs
     */
    private static function _tagNamesToTagIDs($tags)
    {
        $sql = "SELECT tagID FROM tag WHERE tagName IN ('" . implode('\',\'', $tags) . "');";
        $searchResults = self::$_databaseHandle->query($sql);
        $ids = array();
        foreach ($searchResults as $result) {
            array_push($ids, $result['tagID']);
        }
        return $ids;
    }
}