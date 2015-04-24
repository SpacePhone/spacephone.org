(function($) {
    $(document).ready(function() {
        var q = $('#q');
        var m = ['', 'abc', 'def', 'ghi', 'jkl', 'mno', 'pqrs', 'tuv', 'wxyz'];
        q.keyup(function(event) {
            console.log(q.val());
            var n = [];
            $.each(q.val().split(''), function(i, v) {
                if (parseInt(v) === NaN) {
                    $.each(m, function(j, c) {
                        if (new RegExp('[' + c + ']').test(v)) {
                            n.push(j);
                        }
                    });
                } else {
                    n.push(v);
                }
            });
            q.val(n.join(''));
        });
        return true;
    });
})(jQuery);
