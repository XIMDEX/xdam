import React from "react";
import styled, { keyframes } from "styled-components";
import PropTypes from "prop-types";
import LazyLoad from "react-lazyload";

const ImageWrapper = styled.div`
  position: relative;
  width: 100%;
  height: 140px;
`;

const loadingAnimation = keyframes`
  0% {
    background-color: #fff;
  }
  50% {
    background-color: #ccc;
  }
  100% {
    background-color: #fff;
  }
`;

const Placeholder = styled.div`
  position: absolute;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  animation: ${loadingAnimation} 1s infinite;
`;

const StyledImageGrid = styled.img`
  position: absolute;
  left: 0;
  width: 100%;
  height: 100%;
  max-height: 120px;
  object-fit: contain;
`;

const StyledImageList = styled.img`
  position: absolute;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
`;

const LazyImage = ({ src, alt, grid = false }) => {
  const refPlaceholder = React.useRef();

  const removePlaceholder = () => {
    (refPlaceholder.current as any).remove();
  };

  return (
    <ImageWrapper>
      <Placeholder ref={refPlaceholder} />
      <LazyLoad>
        {grid ? (
          <StyledImageGrid
            onLoad={removePlaceholder}
            onError={removePlaceholder}
            src={src}
            alt={alt}
          />
        ) : (
          <StyledImageList
            onLoad={removePlaceholder}
            onError={removePlaceholder}
            src={src}
            alt={alt}
          />
        )}
        
      </LazyLoad>
    </ImageWrapper>
  );
};

LazyImage.propTypes = {
  src: PropTypes.string.isRequired,
  alt: PropTypes.string.isRequired
};

export default LazyImage;