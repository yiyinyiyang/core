import Modal from './Modal';

export default class RequestErrorModal extends Modal {
  className() {
    return 'RequestErrorModal Modal--large';
  }

  title() {
    return this.props.error.xhr
      ? `${this.props.error.xhr.status} ${this.props.error.xhr.statusText}`
      : '';
  }

  content() {
    const { error, formattedError } = this.props;

    let responseText;

    if (!formattedError) {
      try {
        const json = error.response || JSON.parse(error.responseText);

        responseText = JSON.stringify(json, null, 2);
      } catch (e) {
        responseText = error.responseText;
      }
    } else {
      responseText = formattedError.join('\n\n');
    }

    return <div className="Modal-body">
      <pre>
        {this.props.error.options.method} {this.props.error.options.url}<br/><br/>
        {responseText}
      </pre>
    </div>;
  }
}
