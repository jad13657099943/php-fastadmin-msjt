<?php

namespace app\common\library;
/**
 * 搜索联想类
 * @param $strList 数组
 * @param $prefix 词
 */
class TrieTree
{
    private $tree;

    public function __construct($strList)
    {
        $this->tree = $this->buildTrieTree($strList);
    }

    public function queryPrefix($prefix)
    {
        $charArray = preg_split('/(?<!^)(?!$)/u', $prefix);
        $subTree = $this->findSubTree($charArray, $this->tree);

        $words = $this->traverseTree($subTree);

        foreach ($words as &$word) {
            $word = $prefix . $word;
        }
        return $words;
    }

    public function findSubTree($charArray, $tree)
    {
        foreach ($charArray as $char) {
            if (in_array($char, array_keys($tree))) {
                $tree = $tree[$char];
            } else {
                return [];
            }
        }
        return $tree;
    }

    public function traverseTree($tree)
    {
        $words = [];
        foreach ($tree as $node => $subTree) {
            if (empty($subTree)) {
                $words[] = $node;
                return $words;
            }

            $chars = $this->traverseTree($subTree);
            foreach ($chars as $char) {
                $words[] = $node . $char;
            }
        }
        return $words;
    }

    /**
     * 将字符串的数组构建成Trie树
     *
     * @param [array] $strList
     * @return void
     */
    public function buildTrieTree($strList)
    {
        $tree = [];
        foreach ($strList as $str) {
            $charArray = preg_split('/(?<!^)(?!$)/u', $str);
            $tree = $this->addWordToTrieTree($charArray, $tree);
        }
        return $tree;
    }

    /**
     * 把一个词加入到Trie树中
     *
     * @param [type] $charArray
     * @param [type] $tree
     * @return void
     */
    public function addWordToTrieTree($charArray, $tree)
    {
        if (count($charArray) == 0) {
            return [];
        }
        $char = $charArray[0];

        $leftStr = array_slice($charArray, 1);
        $tree[$char] = $this->addWordToTrieTree($leftStr, $tree[$char]);

        return $tree;
    }

    public function getTree()
    {
        return $this->tree;
    }
}



