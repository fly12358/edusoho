import Chooser from './chooser';

class CourseFileChoose extends Chooser {

  constructor($container) {
    super();
    this.container = $container;
    this._init();
    this._initEvent();
  }

  _init() {
    this._loadList();
  }

  _initEvent() {
    $(this.container).on('click', '.pagination a', this._paginationList.bind(this));
    $(this.container).on('click', '.file-browser-item', this._onSelectFile.bind(this));
    $(this.container).on('click', '.js-course-browser-search', this._filterByFileName.bind(this))
    // $('.js-choose-trigger').on('click', this._open.bind(this))
  }

  _loadList() {
    let $containter = $('.course-file-browser');
    let url = $containter.data('url');
    $.get(url, { 'type': $("input[name=type]").val() , 'keyword': $("input[name='searchFileName']").val()}, html => {
      $containter.html(html);
    });
  }

  _paginationList(event) {
    event.stopImmediatePropagation();
    this._loadList();
  }

_filterByFileName() {
    $('input[name=searchFileName]').val($('.js-course-file-name').val());
    this._loadList();
}

  _onSelectFile(event) {
    $('.file-browser-item').removeClass('active');
    var $that = $(event.currentTarget).addClass('active');
    var $that = $(event.currentTarget);
    var file = $that.data();
    this._onChange(file);
    
    // this._close();
  }

  _onChange(file) {
    var value = file ? JSON.stringify(file) : '';
    console.log('begin courseFileChoose:select');
    this.emit('select', file);
    $('[data-role="placeholder"]').html(file.name);

    this._fillMinuteAndSecond(file.length);
  }

  _fillMinuteAndSecond(fileLength) {
    let minute = parseInt(fileLength / 60);
    let second = Math.round(fileLength % 60);
    $("#minute").val(minute);
    $("#second").val(second)
  }

}

export default CourseFileChoose;
