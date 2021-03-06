<?php


use Nette\Utils\Image;


class DefaultImageProvider implements \DotBlue\WebImages\IRepository
{

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param $config array
	 */
	public function configure(array $config) {
		$this->config = $config;
	}

	protected function pathFromId($id){
		$path = $this->config['wwwDir'] . $this->config['path'] . '/' . substr($id, 0, 2) .'/';
		return $path;
	}

	/**
	 * @param \DotBlue\WebImages\ImageRequest $request
	 * @return Image
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function getImage(\DotBlue\WebImages\ImageRequest $request)
	{
		$id = $request->getId();
		$namespace = $request->getNamespace();
		$type = $request->getType();
		$parameters = $request->getParameters();

		list($width, $height) = $this->config['image'][$namespace][$type]['size'];

		switch ($this->config['image'][$namespace][$type]['resize']) {
			case 'cut':
				$resize = Image::EXACT;
				break;
			default:
				$resize = null;
		}
		if (!$height) {
			$resize = Image::FIT; //wrong resize type protection
		}

		$path = $this->pathFromId($id) . $id . '.jpg';

		if (is_file($path)) {
			$image = Image::fromFile($path);
			$image->resize($width, $height, $resize);
			return $image;
		}
		return null;
	}

	/** @param array $cfg
	 * @param string $type
	 * @param \Nette\Http\FileUpload|\Nette\Utils\Image $file
	 * @return string
	 */
	public function saveImage($file)
	{
		/** @var \Nette\Utils\Image $image */
		$image = null;
		if (get_class($file) == 'Nette\Http\FileUpload' && $file->isOk()) {
			$image = Image::fromFile($file->getTemporaryFile());
			$id = md5(time());
		} elseif (get_class($file) == 'Nette\Utils\Image') {
			$image = $file;
			$id = md5(time() + $image->getImageResource());
		} else {
			return null;
		}

		$path = $this->pathFromId($id);
		if (!file_exists($path)) {
			mkdir($path);
		}
		$image->save($path . $id . '.jpg', $this->config['quality'], Image::JPEG);
		return $id;
	}

}
