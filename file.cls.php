<?php
/************************************************************************************************************
#	PHP Version 1.5   MySQL  JavaScript
#	Copyright (c) 2009 http://www.cnalog.com
#	Author: Li Zhixiao <English Name: Hawking E-mail:lzxbit2008@yahoo.com.cn QQ:578731186>
#	Date: 2009/10/10
# 	Modified by: Li Zhixiao
************************************************************************************************************/

/**
 * 功能：文件上传相关操作，也可以作其他操作，如文件删除，创建文件夹等
 * 使用示例：$file=new FILE($vars);
 * DEMO参考最后几行代码
 */

class FILE{
	private $filename='';
	private $savepath='';
	private $filesize=0;
	private $filetype='';
	private $auto_filename='';
	private $is_up_succeed=false;
	
	/**
	* 功能：构造函数
	*
	*/
	public function __construct($vars=null){
		if($vars!=null){
			$this->upload($vars);
		}
	}
	
	/**
	* 功能：开始上传
	*
	*/
	public function upload($vars){
		var_dump($vars);
		if(sizeof($vars)>0){
			$this->set_filesize($_FILES[$vars['file']]['size']);
			$this->set_filetype($_FILES[$vars['file']]['type']);

			if($_FILES[$vars['file']]['size']==0){
				$this->set_is_up_succeed(false);
				return false;
			}
			if($_FILES[$vars['file']]['size']>$vars['limit_size']){
				$this->set_is_up_succeed(false);
				return false;
			}
			if($this->is_path_exist($vars['rootpath'].$vars['savepath'])){
				if($vars['old_file']!=''){
					$this->del_file($vars['old_file']);
				}
				$this->set_savepath($vars['savepath']);
				$this->set_filename($_FILES[$vars['file']]['name']);
				$this->set_auto_filename($_FILES[$vars['file']]['name']);
				if($this->check_allowed_ext($vars['allowed_ext'],$_FILES[$vars['file']]['name'])){
					if(move_uploaded_file($_FILES[$vars['file']]['tmp_name'],$vars['rootpath'].$this->get_savepath().$this->get_auto_filename())){
						$this->set_is_up_succeed(true);
						return true;
					}
					$this->set_is_up_succeed(false);
					return false;
				}
				$this->set_is_up_succeed(false);
				return false;
			}
			$this->set_is_up_succeed(false);
			return false;
		}
	}
	
	/**
	* 功能：判断是否允许上传该文件
	*
	*/
	public function check_allowed_ext($allowed_ext,$filename){
		if($allowed_ext=='' || $allowed_ext=='*' || strpos($allowed_ext,strtolower(substr($filename,strrpos($filename,'.'))))>-1){
			return true;
		}
		return false;
	}
	
	/**
	* 功能：获取上传状态
	*
	*/
	public function get_is_up_succeed(){
		return $this->is_up_succeed;
	}
	
	/**
	* 功能：设置上传状态
	*
	*/
	public function set_is_up_succeed($boolean){
		return $this->is_up_succeed=$boolean;
	}
	
	/**
	* 功能：设置文件大小
	*
	*/
	public function set_filesize($filesize){
		return $this->filesize=$filesize;
	}
	
	/**
	* 功能：获取文件大小
	*
	*/
	public function get_filesize(){
		return $this->filesize;
	}
	
	/**
	* 功能：获取文件扩展名
	*
	*/
	public function get_file_ext(){
		$tmparr=explode('.',$this->auto_filename);
		return $tmparr[1];
	}

	/**
	* 功能：设置文件类型
	*
	*/
	public function set_filetype($filetype){
		return $this->filetype=$filetype;
	}
	
	/**
	* 功能：获取文件类型
	*
	*/
	public function get_filetype(){
		return $this->filetype;
	}

	/**
	* 功能：设置文件实际路径
	*
	*/
	public function set_savepath($savepath){
		return $this->savepath=$savepath;
	}
	
	/**
	* 功能：获取文件实际路径
	*
	*/
	public function get_savepath(){
		return $this->savepath;
	}

	/**
	* 功能：设置真实的文件名
	*
	*/
	public function set_filename($filename){
		return $this->filename=$filename;
	}
	
	/**
	* 功能：获取真实的文件名
	*
	*/
	public function get_filename(){
		return $this->filename;
	}

	/**
	* 功能：获取默认的文件名
	*
	*/
	public function get_auto_filename(){
		return $this->auto_filename;
	}

	/**
	* 功能：设置默认的文件名
	*
	*/
	function set_auto_filename($filename){
		$timeStr=microtime();
		$timeArr=explode(' ',$timeStr);
		$tmp_filename=$timeArr[1].substr($timeArr[0],2);
		$tmp_filename=sha1(md5(strval($tmp_filename)).mt_rand(1,100));
		$tmp_file_ext=substr($filename,strrpos($filename,'.'));
		$this->auto_filename=$tmp_filename.$tmp_file_ext;
	}

	/**
	* 功能：检查文件夹是否存在，不存在则新建
	*
	*/
	public function is_path_exist($savepath){
		if($savepath=='') 
			return true;
		if(is_dir($savepath)){
			return true;
		}
		else{
			if($this->mkdirs($savepath))
				return true;
			else
				return false;
		}
	}
	
	/**
	* 功能：对上传的文件处理后重新命名（删除原上传后的文件）
	*
	*/
	public function rename_file($new_name,$old_name){
		if(@rename($new_name,$old_name))
			return 1;
		else
			return 0;
	}
	
	/**
	* 功能：删除文件
	*
	*/
	public function del_file($filepath){
		if(@unlink($filepath))
			return 1;
		else
			return 0;
	}
	
	/**
	* 检查目标文件夹是否存在，如果不存在则自动创建该目录
	* @access      public
	* @param       string      folder     目录路径。不能使用相对于网站根目录的URL
	* @return      bool
	*/
	public function mkdirs($folder){
		$reval = false;	
		if (!file_exists($folder)){
			/* 如果目录不存在则尝试创建该目录 */
			@umask(0);	
			/* 将目录路径拆分成数组 */
			preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);	
			/* 如果第一个字符为/则当作物理路径处理 */
			$base = ($atmp[0][0] == '/') ? '/' : '';	
			/* 遍历包含路径信息的数组 */
			foreach ($atmp[1] AS $val){
				if ('' != $val){
					$base .= $val;	
					if ('..' == $val || '.' == $val){
						/* 如果目录为.或者..则直接补/继续下一个循环 */
						$base .= '/';	
						continue;
					}
				}
				else{
					continue;
				}	
				$base .= '/';	
				if (!file_exists($base)){
					/* 尝试创建目录，如果创建失败则继续循环 */
					if (@mkdir(rtrim($base, '/'), 0777)){
						@chmod($base, 0777);
						$reval = true;
					}
				}
			}
		}
		else{
			/* 路径已经存在。返回该路径是不是一个目录 */
			$reval = is_dir($folder);
		}	
		clearstatcache();	
		return $reval;
	}
	
	/**
	* 把数据写入某个文件
	**/
	function write2file($filename,$str){
		$fp=fopen($filename,'w');
		@fwrite($fp,$str);
		@fclose($fp);
	}

}
